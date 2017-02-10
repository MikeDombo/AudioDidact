<?php

/**
 * Class CRTV
 */
class CRTV extends SupportedSite{
	// Setup global variables
	/** @var string YouTube URL */
	private $brightcoveBaseURL = "https://secure.brightcove.com/services/mobile/streaming/index/master.m3u8";
	private $pubId;
	private $thumbnail_url;

	/**
	 * CRTV constructor. Gets the video information, checks for it in the user's feed.
	 *
	 * @param string $str
	 * @param PodTube $podtube
	 * @throws \Exception
	 */
	public function __construct($str, PodTube $podtube){
		parent::$podtube = $podtube;
		$this->video = new Video();

		// If there is a URL/ID, continue
		if($str != null){
			$this->video->setURL($str);

			// Set video ID and time to current time
			$d = $this->getVideoId($str);
			$this->video->setId($d["ID"]);
			$this->video->setTime(time());

			$this->getPublisherID($d["html"]);

			// Check if the video already exists in the DB. If it does, then we do not need to get the information again
			if(!parent::$podtube->isInFeed($this->video->getId())){
				// Get video author, title, and description from YouTube API
				$info = $this->getVideoInfo($d["html"]);
				$this->video->setTitle($info["title"]);
				$this->video->setAuthor("CRTV");
				$this->video->setDesc($info["description"]);
			}
			else{
				$this->video = parent::$podtube->getDataFromFeed($this->video->getId());
			}
		}
	}

	private function getVideoId($str){
		// Download CRTV video page
		$crtv_html = file_get_contents($str);

		// Setup CRTV webpage parsing objects
		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($crtv_html);
		$finder = new DomXPath($doc);

		// Get Thumbnail
		$thumbnail_url = "";
		$nodes = $finder->query("//meta[@property='og:image']");
		foreach($nodes as $i){
			$thumbnail_url = $i->getAttribute('content');
		}
		parse_str(parse_url($thumbnail_url, PHP_URL_QUERY), $query);
		$videoId = $query["videoId"];

		$this->thumbnail_url = $thumbnail_url;

		return ["ID" => $videoId, "html" => $crtv_html];
	}

	private function getPublisherID($html){
		// Get Brightcove Publisher/Account ID
		$m = preg_match('/dataAc{1,2}ountId:\s+[\'\"](\d+)[\'\"]/', $html, $matches);
		$pubId = $matches[1];
		$this->pubId = $pubId;
	}

	private function getVideoInfo($html){
		// Get Video Title
		$m = preg_match('/\<title\>([^\<]*)\<\/title\>/', $html, $matches);
		$video_title = $matches[1];

		// Get video description
		$m = preg_match('/\<div class=[\"\']rtf[\'\"]\>\s*\<p\>(.*)\<\/p\>\s*\<\/div\>/', $html, $matches);
		$desc = $matches[1];

		return ["title" => $video_title, "description" => $desc];
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
			YouTube::getDuration($downloadFilePath.".mp4") == YouTube::getDuration($downloadFilePath.".mp3")
			&& YouTube::getDuration($downloadFilePath.".mp3")){
			return true;
		}
		// If only the mp4 is downloaded (and has a duration) or the mp3 duration is null, then convert the mp4 to mp3
		if(file_exists($downloadFilePath.".mp4") && YouTube::getDuration($downloadFilePath.".mp4")){
			$this->convert();
			return true;
		}
		// If all else fails, return false
		return false;
	}

	/**
	 * Download thumbnail using videoID from Brightcove
	 */
	public function downloadThumbnail(){
		$thumbFilename = $this->video->getID().".jpg";
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$thumbnail = $path . $thumbFilename;
		file_put_contents($thumbnail, fopen($this->thumbnail_url, "r"));
		// Set the thumbnail file as publicly accessible
		@chmod($thumbnail, 0775);
	}

	public function downloadVideo(){
		$id = $this->video->getID();
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$videoFilename = "$id.mp4";
		$videoPath = $path . $videoFilename;

		// Based on gathered information, generate Brightcove query parameters to get the HLS playlist
		$m3u8_url = $this->brightcoveBaseURL."?videoId=".$this->video->getId()."&pubId=".$this->pubId."&secure=true";
		$cmd = "ffmpeg -i \"".$m3u8_url."\" -map 0:p:1 -y -c copy -bsf:a aac_adtstoasc \"".$videoPath."\" 1> "
		.$this->video->getID().".txt 2>&1";

		// Check if we're on Windows or *nix
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			// Start the command in the background
			pclose(popen("start /B ".$cmd, "r"));
		}
		else {
			pclose(popen($cmd." &", "r"));
		}

		$progress = 0;
		// Get the download and conversion progress and output the progress to the UI using a JSON array
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
			$response = array('stage' =>0, 'progress' => $progress);
			echo json_encode($response);
			usleep(500000);
		}
		// Delete the temporary file that contained the ffmpeg output
		@unlink($this->video->getID().".txt");
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
}
