<?php

class SoundCloud extends SupportedSite{
	// Setup global variables
	private $streams_base_url = "https://api.soundcloud.com/tracks/XYZ/streams?client_id=fDoItMDbsbZz8dY16ZzARCZmzgHBPotA";
	private $thumbnail_url;
	private $audioJSON;

	/**
	 * SoundCloud constructor. Gets the audio information, checks for it in the user's feed.
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
			$info = $this->getVideoInfo($str);
			$this->video->setId($info["ID"]);
			$this->video->setTime(time());

			// Check if the video already exists in the DB. If it does, then we do not need to get the information again
			if(!parent::$podtube->isInFeed($this->video->getId())){
				$this->video->setTitle($info["title"]);
				$this->video->setAuthor($info["author"]);
				$this->video->setDesc($info["description"]);
			}
			else{
				$this->video = parent::$podtube->getDataFromFeed($this->video->getId());
			}
		}
	}

	private function curl_http_get($url, $ssl = false) {
		$ch = curl_init($url);
		$headers = array(
			"User-Agent: curl/7.16.3 (i686-pc-cygwin) libcurl/7.16.3 OpenSSL/0.9.8h zlib/1.2.3 libssh2/0.15-CVS",
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
			"Accept-Language: en-us;q=0.5,en;q=0.3",
			"Keep-Alive: 115",
			"Connection: keep-alive"
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if ($ssl) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		$response = curl_exec($ch);
		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($response_code != 200 && $response_code != 302 && $response_code != 304) {
			$response = false;
		}
		return $response;
	}

	private function getVideoInfo($str){
		$str = str_replace("http://", "https://", $str);
		$webpage = $this->curl_http_get($str, true);
		preg_match("/var c=([^;]*)/i", $webpage, $matches);
		$brackets = 0;
		$firstRun = true;
		$strlen = strlen($matches[1]);
		for($i = 0; $i < $strlen; $i++) {
			if(!$firstRun && $brackets == 0){
				break;
			}
			if($firstRun){
				$firstRun = false;
			}
			$char = $matches[1][$i];
			if($char == "["){
				$brackets += 1;
			}
			else if($char == "]"){
				$brackets -= 1;
			}
		}
		$json = substr($matches[1], 0, $i);
		$this->audioJSON = json_decode($json, true);

		foreach($this->audioJSON as $a){
			$a = $a["data"][0];
			if(isset($a["title"]) && isset($a["uri"]) && isset($a["description"])){
				$id = explode("/", $a["uri"]);
				$videoId = $id[count($id)-1];
				$description = $a["description"];
				$title = $a["title"];
				$author = $a["user"]["username"];
				if (isset($a["artwork_url"]) && $a["artwork_url"] != null){
					$this->thumbnail_url = str_replace("large.jpg", "t500x500.jpg", $a["artwork_url"]);
				}
				else{
					$this->thumbnail_url = str_replace("large.jpg", "t500x500.jpg", $a["user"]["avatar_url"]);
				}
				return ["ID" => $videoId, "description" => $description, "title" => $title, "author" => $author];
			}
		}
		error_log("SoundCloud failed to parse JSON for URL: ".$str);
		return false;
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
		// If the mp3 check if the mp3 has a duration that is not null
		if(file_exists($downloadFilePath.".mp3") && YouTube::getDuration($downloadFilePath.".mp3")){
			return true;
		}
		// If all else fails, return false
		return false;
	}

	/**
	 * Download thumbnail
	 */
	public function downloadThumbnail(){
		$thumbFilename = $this->video->getID().".jpg";
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$thumbnail = $path . $thumbFilename;
		file_put_contents($thumbnail, fopen($this->thumbnail_url, "r"));
		// Set the thumbnail file as publicly accessible
		@chmod($thumbnail, 0775);
	}

	private function getDownloadURL(){
		$streamsURL = str_replace("/XYZ/", "/".$this->video->getId()."/", $this->streams_base_url);
		$streamsJSON = file_get_contents($streamsURL);
		$streamsJSON = json_decode($streamsJSON, true);
		return $streamsJSON["http_mp3_128_url"];
	}

	public function downloadVideo(){
		$id = $this->video->getID();
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$videoFilename = "$id.mp3";
		$videoPath = $path . $videoFilename;
		$url = $this->getDownloadURL();

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
			$local = fopen($videoPath, 'w');

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
			throw new Exception("Downloaded audio length was 0, please try again later");
		}

		return true;
	}

	/**
	 * Since SoundCloud is audio only, we do not convert, but only add the album artwork
	 */
	public function convert(){
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$ffmpeg_albumArt = $path.$this->video->getID().".jpg";
		$ffmpeg_outfile = $path . $this->video->getID() .".mp3";
		$ffmpeg_tempFile = $path . $this->video->getID() ."-art.mp3";

		exec("ffmpeg -i \"$ffmpeg_outfile\" -i \"$ffmpeg_albumArt\" -y -c copy -map 0 -map 1 -id3v2_version 3 -metadata:s:v title=\"Album cover\" -metadata:s:v comment=\"Cover (Front)\"  \"$ffmpeg_tempFile\"");
		rename($ffmpeg_tempFile, $ffmpeg_outfile);

		$this->video->setDuration(YouTube::getDurationSeconds($ffmpeg_outfile));

		// Send progress to UI
		$response = array('stage' =>1, 'progress' => 100);
		echo json_encode($response);
		return;
	}
}