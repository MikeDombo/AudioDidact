<?php

/**
 * Class YouTube
 */
class YouTube extends SupportedSite{
	// Setup global variables
	/** @var string YouTube URL */
	private $YouTubeBaseURL = "http://www.youtube.com/";

	/**
	 * YouTube constructor. Gets the video information, checks for it in the user's feed.
	 *
	 * @param string $str
	 * @param PodTube $podtube
	 * @throws \Exception
	 */
	public function __construct($str, PodTube $podtube){
		parent::$podtube = $podtube;
		$this->video = new Video();

		// Make download folder if it does not exist
		if(!file_exists(DOWNLOAD_PATH)){
			mkdir(DOWNLOAD_PATH);
		}
		// If there is a URL/ID, continue
		if($str != null){
			$this->video->setURL($str);

			// Set video ID from setYoutubeID and time to current time
			$this->video->setId($this->setYoutubeID($str));
			$this->video->setTime(time());

			// Check if the video already exists in the DB. If it does, then we do not need to get the information
			// from the YouTube API again
			if(!parent::$podtube->isInFeed($this->video->getId())){
				// Get video author, title, and description from YouTube API
				$info = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet&id="
					.$this->video->getId().
					"&fields=items/snippet/description,items/snippet/title,items/snippet/channelTitle&key=".
					GOOGLE_API_KEY), true);
				// If the lookup fails, send this error to the UI as a JSON array
				if(!isset($info['items'][0]['snippet'])){
					$this->echoErrorJSON("ID Inaccessible");
					throw new Exception("Download Failed!");
				}
				$info = $info['items'][0]['snippet'];
				$this->video->setTitle($info["title"]);
				$this->video->setAuthor($info["channelTitle"]);
				$this->video->setDesc($info["description"]);
			}
			else{
				$this->video = parent::$podtube->getDataFromFeed($this->video->getId());
			}
		}
	}

	/**
	 * Set YouTube ID from a given string using parseYoutubeURL
	 * @param string $str
	 * @return bool
	 * @throws \Exception
	 */
	private function setYoutubeID($str){
		// Try and parse the string into a usable ID
		$tmpId = $this->parseYoutubeURL($str);
		$vidId = ($tmpId !== false) ? $tmpId : $str;
		if(strpos($vidId, "/playlist") > -1){
			$this->echoErrorJSON("URL is a playlist. PodTube does not currently support playlists.");
			throw new Exception("Cannot download playlist");
		}
		if(strpos($vidId, "/c/") > -1 || strpos($vidId, "/channel/") > -1 || strpos($vidId, "/user/") > -1){
			$this->echoErrorJSON("URL is a channel. PodTube does not, and likely will not ever, support downloading of channels.");
			throw new Exception("Cannot download channel");
		}
		$url = sprintf($this->YouTubeBaseURL . "watch?v=%s", $vidId);
		// Get HTTP status of the video url and make sure that it is
		// 200 = OK
		// 301 = Moved Permanently
		// 302 = Moved Temporarily
		if($this->curl_httpstatus($url) !== 200 && $this->curl_httpstatus($url) !== 301 && $this->curl_httpstatus($url)
			!== 302){
			throw new Exception("Invalid Youtube video ID: $vidId");
		}
		return $vidId;
	}

	/**
	 * Checks if all thumbnail, video, and mp3 are downloaded and have a length (ie. video or audio are not null)
	 * @return bool
	 */
	public function allDownloaded(){
		$downloadFilePath = DOWNLOAD_PATH.DIRECTORY_SEPARATOR.$this->video->getID();
		// If the thumbnail has not been downloaded, go ahead and download it
		if(!file_exists($downloadFilePath.".jpg")){
			$this->downloadThumbnail();
		}
		// If the mp3 and mp4 files exist, check if the mp3 has a duration that is not null
		if(file_exists($downloadFilePath.".mp3") && file_exists($downloadFilePath.".mp4") &&
			$this->getDuration($downloadFilePath.".mp4") == $this->getDuration($downloadFilePath.".mp3")
			&& $this->getDuration($downloadFilePath.".mp3")){
				return true;
		}
		// If only the mp4 is downloaded (and has a duration) or the mp3 duration is null, then convert the mp4 to mp3
		if(file_exists($downloadFilePath.".mp4") && $this->getDuration($downloadFilePath.".mp4")){
			$this->convert();
			return true;
		}
		// If all else fails, return false
		return false;
	}

	/**
	 * Download thumbnail using videoID from YouTube's image server
	 */
	public function downloadThumbnail(){
		$thumbFilename = $this->video->getID().".jpg";
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$thumbnail = $path . $thumbFilename;
		file_put_contents($thumbnail, fopen("https://i.ytimg.com/vi/".$this->video->getID()."/mqdefault.jpg", "r"));
		// Set the thumbnail file as publicly accessible
		@chmod($thumbnail, 0775);
	}

	/**
	 * Download video using download URL from Python script and then call downloadWithPercentage to actually download the video
	 */
	public function downloadVideo(){
		$id = $this->video->getID();
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$videoFilename = "$id.mp4";
		$videoPath = $path . $videoFilename;

		$url = $this->getDownloadURL($id);
		if(strpos($url, "Error:")>-1){
			$this->echoErrorJSON($url);
			throw new Exception("Download Failed!");
		}
		try{
			/* Actually download the video from the url and print the
			 * percentage to the screen with JSON
			 */
			$this->downloadWithPercentage($url, $videoPath);
			// Set the video file as publicly accessible
			@chmod($videoPath, 0775);
			return;
		}
		catch(Exception $e){
			$this->echoErrorJSON($e->getMessage());
			throw $e;
		}
	}

	/**
	 * Gets lowest quality mp4 download url based on a given id.
	 * @param $id
	 * @return string
	 * @throws \Exception
	 */
	private function getDownloadURL($id){
		$url = $this->YouTubeBaseURL."watch?v=".$id;
		$html = file_get_contents($url);
		$restriction_pattern = "og:restrictions:age";

		if(strpos($html, $restriction_pattern)>-1){
			return "Error: Age restricted video. Unable to download.";
		}
		$json_start_pattern = "ytplayer.config = ";
		$pattern_idx = strpos($html, $json_start_pattern);
		# In case video is unable to play
		if($pattern_idx == -1){
			return "Error: Unable to find start pattern.";
		}

		$start = $pattern_idx + strlen($json_start_pattern);
		$html = substr($html, $start);

		$unmatched_brackets_num = 0;
		$index = 1;
		$htmlArr = str_split($html);
		foreach($htmlArr as $i=>$ch){
			if($ch == "{"){
				$unmatched_brackets_num += 1;
			}
			else if($ch == "}"){
				$unmatched_brackets_num -= 1;
				if($unmatched_brackets_num == 0){
					break;
				}
			}
		}
		$offset = $index+$i;

		$json_object = json_decode(substr($html, 0, $offset), true);

		if(isset($json_object["args"]["livestream"]) && $json_object["args"]["livestream"] && (!isset($json_object["args"]["url_encoded_fmt_stream_map"]) || $json_object["args"]["url_encoded_fmt_stream_map"] == "")){
			$this->echoErrorJSON("<h3>Download Failed</h3><h4>This URL is a livestream, try again when the stream has ended</h4>");
			throw new Exception("Download Failed!");
		}
		//isset($json_object["args"]["live_default_broadcast"]) && $json_object["args"]["live_default_broadcast"] == 1
		if(!isset($json_object["args"]["url_encoded_fmt_stream_map"]) || $json_object["args"]["url_encoded_fmt_stream_map"] == ""){
			$this->echoErrorJSON("<h3>Download Failed</h3><h4>Try again later</h4>");
			throw new Exception("Download Failed!");
		}
		$encoded_stream_map = $json_object["args"]["url_encoded_fmt_stream_map"];
		$dct = array();
		$videos = explode(",", $encoded_stream_map);
		foreach($videos as $i=>$video){
			$video = explode("&", $video);
			foreach($video as $v){
				$key = explode("=", $v)[0];
				$value = explode("=", $v)[1];
				$dct[$key][] = urldecode($value);
			}
		}
		$json_object["args"]["stream_map"] = $dct;
		$stream_map = $dct;
		unset($dct, $videos, $html, $htmlArr, $json_object);

		$video_urls = $stream_map["url"];
		$downloads = [];
		foreach($video_urls as $i=>$vurl){
			$quality_profile = $this->getQualityProfilesFromURL($vurl);
			$downloads[] = ["url"=>$vurl, "ext"=>$quality_profile["extension"], "res"=>$quality_profile["resolution"]];
		}
		$downloadURL = "";
		$resolution = 999999;
		// Find lowest quality mp4
		foreach($downloads as $v){
			if($v["ext"] == "mp4" && intval(substr($v["res"], 0, -1)) < $resolution){
				$resolution = intval(substr($v["res"], 0, -1));
				$downloadURL = $v["url"];
			}
		}

		return $downloadURL;
	}

	/**
	 * Returns a quality profile or false based on a url.
	 * @param $url
	 * @return bool|mixed
	 */
	private function getQualityProfilesFromURL($url){
		$qp = [];
		$qp[5] = ["flv", "240p", "Sorenson H.263", "N/A", "0.25", "MP3", "64"];
		$qp[17] = ["3gp", "144p", "MPEG-4 Visual", "Simple", "0.05", "AAC", "24"];
		$qp[36] = ["3gp", "240p", "MPEG-4 Visual", "Simple", "0.17", "AAC", "38"];
		$qp[43] = ["webm", "360p", "VP8", "N/A", "0.5", "Vorbis", "128"];
		$qp[100] = ["webm", "360p", "VP8", "3D", "N/A", "Vorbis", "128"];
		$qp[18] = ["mp4", "360p", "H.264", "Baseline", "0.5", "AAC", "96"];
		$qp[22] = ["mp4", "720p", "H.264", "High", "2-2.9", "AAC", "192"];
		$qp[82] = ["mp4", "360p", "H.264", "3D", "0.5", "AAC", "96"];
		$qp[83] = ["mp4", "240p", "H.264", "3D", "0.5", "AAC", "96"];
		$qp[84] = ["mp4", "720p", "H.264", "3D", "2-2.9", "AAC", "152"];
		$qp[85] = ["mp4", "1080p", "H.264", "3D", "2-2.9", "AAC", "152"];
		foreach($qp as $k=>$q){
			$keys = ["extension","resolution","video_codec","profile","video_bitrate","audio_codec","audio_bitrate"];
			foreach($keys as $k2=>$v){
				$qp[$k][$v] = $q[$k2];
			}
			foreach($qp[$k] as $key=>$value){
				if(!in_array($key, $keys, true)){
					unset($qp[$k][$key]);
				}
			}
		}

		$reg_exp = '/itag=(\d+)/';
		preg_match_all($reg_exp, $url, $itag);
		if(isset($itag[1][0]) && intval($itag[1][0]) > -1){
			$itag = intval($itag[1][0]);
			return $qp[$itag];
		}
		return false;
	}

	/**
	 * Download the video to $localFile with a given $url
	 * While downloading output progress to UI as JSON array
	 * @param $url
	 * @param $localFile
	 * @return bool
	 * @throws \Exception
	 */
	private function downloadWithPercentage($url, $localFile){
		// Use CURL to get the download content length in order to print the progress
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);
		if ($data === false) {
			$this->echoErrorJSON("Download failed, URL tried was ".$url);
			throw new Exception("Download Failed!");
		}

		// Get content length in bytes
		$contentLength = 'unknown';
		if (preg_match_all('/Content-Length: (\d+)/', $data, $matches)) {
			$contentLength = (int)$matches[count($matches)-1][count($matches[count($matches)-1])-1];
		}

		if(intval($contentLength)>0){
			// Open local and remote files for write and read respectively
			$remote = fopen($url, 'r');
			$local = fopen($localFile, 'w');

			$read_bytes = 0;
			// Read until the end of the remote file
			while(!feof($remote)) {
				// Read 4KB and write them to the local file
				$buffer = fread($remote, 4096);
				fwrite($local, $buffer);
				$read_bytes += 4096;

				// Get progress percentage from the read bytes and total length
				$progress = min(100, 100 * $read_bytes / $contentLength);
				// Print progress to the UI using a JSON array
				$response = array('stage' =>0, 'progress' => $progress);
				echo json_encode($response);
			}
			// Close the handles of both files
			fclose($remote);
			fclose($local);
		}
		else{
			error_log("Content length was 0 for URL: ".$url);
			throw new Exception("Downloaded video length was 0, please try again later");
		}

		return true;
	}

	/**
	 * Converts mp4 video to mp3 audio using ffmpeg
	 */
	public function convert(){
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$ffmpeg_infile = $path . $this->video->getID() .".mp4";
		$ffmpeg_albumArt = $path.$this->video->getID().".jpg";
		$ffmpeg_outfile = $path . $this->video->getID() .".mp3";
		$ffmpeg_tempFile = $path . $this->video->getID() ."-art.mp3";

		// Use ffmpeg to convert the audio in the background and save output to a file called videoID.txt
		$cmd = "ffmpeg -i \"$ffmpeg_infile\" -y -q:a 5 -map a \"$ffmpeg_outfile\" 1> ".$this->video->getID().".txt 2>&1";

		// Check if we're on Windows or *nix
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			// Start the command in the background
			pclose(popen("start /B ".$cmd, "r"));
		}
		else {
			pclose(popen($cmd." &", "r"));
		}

		$progress = 0;
		// Get the conversion progress and output the progress to the UI using a JSON array
		while($progress != 100){
			$content = @file_get_contents($this->video->getID().'.txt');
			// Get the total duration of the file
			preg_match("/Duration: (.*?), start:/", $content, $matches);
			// If there is no match, then wait and continue
			if(!isset($matches[1])){
				usleep(500000);
				continue;
			}
			$rawDuration = $matches[1];
			$ar = array_reverse(explode(":", $rawDuration));
			$duration = floatval($ar[0]);
			if (!empty($ar[1])){
				$duration += intval($ar[1]) * 60;
			}
			if (!empty($ar[2])){
				$duration += intval($ar[2]) * 60 * 60;
			}
			preg_match_all("/time=(.*?) bitrate/", $content, $matches);

			// Matches time of the converted file and gets the percentage complete
			$rawTime = array_pop($matches);
			if (is_array($rawTime)){
				$rawTime = array_pop($rawTime);
			}
			$ar = array_reverse(explode(":", $rawTime));
			$time = floatval($ar[0]);
			if (!empty($ar[1])){
				$time += intval($ar[1]) * 60;
			}
			if (!empty($ar[2])){
				$time += intval($ar[2]) * 60 * 60;
			}
			$progress = round(($time/$duration) * 100);

			// Send progress to UI
			$response = array('stage' =>1, 'progress' => $progress);
			echo json_encode($response);
			usleep(500000);
		}
		// Delete the temporary file that contained the ffmpeg output
		@unlink($this->video->getID().".txt");
		exec("ffmpeg -i \"$ffmpeg_outfile\" -i \"$ffmpeg_albumArt\" -y -c copy -map 0 -map 1 -id3v2_version 3 -metadata:s:v title=\"Album cover\" -metadata:s:v comment=\"Cover (Front)\"  \"$ffmpeg_tempFile\"");
		rename($ffmpeg_tempFile, $ffmpeg_outfile);
		return;
	}

	/**
	 * Use cURL to get the HTTP status of a given URL
	 * @param $url
	 * @return int
	 */
	private function curl_httpstatus($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko Firefox/11.0");
		curl_setopt($ch, CURLOPT_REFERER, $this->YouTubeBaseURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_exec($ch);
		$int = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return intval($int);
	}

	/**
	 * Get duration of media file from ffmpeg
	 * @param $file
	 * @return bool|string
	 */
	public static function getDuration($file){
		$dur = shell_exec("ffmpeg -i ".$file." 2>&1");
		if(preg_match("/: Invalid /", $dur)){
			return false;
		}
		preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $dur, $duration);
		if(!isset($duration[1])){
			return false;
		}
		return $duration[1].":".$duration[2].":".$duration[3];
	}

	/**
	 * Parse a YouTube URL to get the video ID
	 * @param $url
	 * @return bool
	 */
	private function parseYoutubeURL($url){
		$pattern = '#^(?:https?://)?';
		$pattern .= '(?:www\.)?';
		$pattern .= '(?:';
		$pattern .=   'youtu\.be/';
		$pattern .=   '|youtube\.com';
		$pattern .=   '(?:';
		$pattern .=     '/embed/';
		$pattern .=     '|/v/';
		$pattern .=     '|/watch\?v=';
		$pattern .=     '|/watch\?.+&v=';
		$pattern .=   ')';
		$pattern .= ')';
		$pattern .= '([\w-]{11})';
		$pattern .= '(?:.+)?$#x';
		preg_match($pattern, $url, $matches);
		return (isset($matches[1])) ? $matches[1] : false;
	}
}
