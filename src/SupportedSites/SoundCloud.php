<?php

namespace AudioDidact\SupportedSites;

use AudioDidact\Video;

class SoundCloud extends SupportedSite {
	// Setup global variables
	private $streamsBaseURL = "https://api.soundcloud.com/tracks/XYZ/streams?client_id=2t9loNQH90kzJcsFCODdigxfp325aq4z";
	private $thumbnailURL;
	private $audioJSON;

	/**
	 * SoundCloud constructor. Gets the audio information, checks for it in the user's feed.
	 *
	 * @param string $str
	 * @param boolean $isVideo
	 * @throws \Exception
	 */
	public function __construct($str, $isVideo){
		$this->video = new Video();

		// If there is a URL/ID, continue
		if($str != null){
			if(!filter_var($str, FILTER_VALIDATE_URL) || !mb_strpos($str, "soundcloud")){
				throw new \Exception("Soundcloud URL is invalid");
			}
			$this->video->setURL($str);
			$this->video->setIsVideo(false);

			// Set video ID and time to current time
			$info = $this->getVideoInfo($str);
			if(!$info){
				throw new \Exception("Soundcloud URL is invalid");
			}
			$this->video->setId($info["ID"]);
			$this->video->setFilename($this->video->getId());
			$this->video->setThumbnailFilename($this->video->getFilename() . ".jpg");
			$this->video->setTime(time());
			$this->video->setTitle($info["title"]);
			$this->video->setAuthor($info["author"]);
			$this->video->setDesc($info["description"]);
		}
	}

	private function cURLHTTPGet($url, $ssl = false){
		$ch = curl_init($url);
		$headers = [
			"User-Agent: curl/7.16.3 (i686-pc-cygwin) libcurl/7.16.3 OpenSSL/0.9.8h zlib/1.2.3 libssh2/0.15-CVS",
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
			"Accept-Language: en-us;q=0.5,en;q=0.3",
			"Keep-Alive: 115",
			"Connection: keep-alive"
		];
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if($ssl){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		$response = curl_exec($ch);
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($responseCode != 200 && $responseCode != 302 && $responseCode != 304){
			$response = false;
		}

		return $response;
	}

	private function getVideoInfo($str){
		$str = str_replace("http://", "https://", $str);
		$webpage = $this->cURLHTTPGet($str, true);
		preg_match("/var c=([^;]*)/i", $webpage, $matches);
		$brackets = 0;
		$firstRun = true;
		$strlen = mb_strlen($matches[1]);
		for($i = 0; $i < $strlen; $i++){
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
		$json = mb_substr($matches[1], 0, $i);
		$this->audioJSON = json_decode($json, true);

		foreach($this->audioJSON as $a){
			$a = $a["data"][0];
			if(isset($a["title"]) && isset($a["uri"]) && isset($a["description"])){
				$id = explode("/", $a["uri"]);
				$videoId = $id[count($id) - 1];
				$description = $a["description"];
				$title = $a["title"];
				$author = $a["user"]["username"];
				if(isset($a["artwork_url"]) && $a["artwork_url"] != null){
					$this->thumbnailURL = str_replace("large.jpg", "t500x500.jpg", $a["artwork_url"]);
				}
				else{
					$this->thumbnailURL = str_replace("large.jpg", "t500x500.jpg", $a["user"]["avatar_url"]);
				}

				return ["ID" => $videoId, "description" => $description, "title" => $title, "author" => $author];
			}
		}
		error_log("SoundCloud failed to parse JSON for URL: " . $str);

		return false;
	}

	/**
	 * Checks if all thumbnail, video, and mp3 are downloaded and have a length (ie. video or audio are not null)
	 *
	 * @return bool
	 */
	public function allDownloaded(){
		$downloadPath = DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$fullDownloadPath = $downloadPath . $this->video->getFilename() . $this->video->getFileExtension();

		// If the thumbnail has not been downloaded, go ahead and download it
		if(!file_exists($downloadPath . $this->video->getThumbnailFilename())){
			$this->downloadThumbnail();
		}
		// If the mp3 check if the mp3 has a duration that is not null
		if(file_exists($fullDownloadPath) && SupportedSite::getDuration($fullDownloadPath)){
			// Before returning true, set the duration since convert will not be run
			$this->video->setDuration(SupportedSite::getDurationSeconds($fullDownloadPath));

			return true;
		}

		// If all else fails, return false
		return false;
	}

	/**
	 * Download thumbnail
	 */
	public function downloadThumbnail(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$thumbnail = $path . $this->video->getThumbnailFilename();
		file_put_contents($thumbnail, fopen($this->thumbnailURL, "r"));
		// Set the thumbnail file as publicly accessible
		@chmod($thumbnail, 0775);
	}

	private function getDownloadURL(){
		$streamsURL = str_replace("/XYZ/", "/" . $this->video->getId() . "/", $this->streamsBaseURL);
		$streamsJSON = file_get_contents($streamsURL);
		$streamsJSON = json_decode($streamsJSON, true);

		return $streamsJSON["http_mp3_128_url"];
	}

	public function downloadVideo(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$videoPath = $path . $this->video->getFilename() . $this->video->getFileExtension();
		$url = $this->getDownloadURL();

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);
		if($data === false){
			$this->echoErrorJSON("Download failed, URL tried was " . $url);
			throw new \Exception("Download Failed!");
		}

		// Get content length in bytes
		$contentLength = 'unknown';
		if(preg_match_all('/Content-Length: (\d+)/', $data, $matches)){
			$contentLength = (int)$matches[count($matches) - 1][count($matches[count($matches) - 1]) - 1];
		}

		if(intval($contentLength) > 0){
			// Open local and remote files for write and read respectively
			$remote = fopen($url, 'r');
			$local = fopen($videoPath, 'w');

			$readBytes = 0;
			// Read until the end of the remote file
			while(!feof($remote)){
				// Read 4KB and write them to the local file
				$buffer = fread($remote, 4096);
				fwrite($local, $buffer);
				$readBytes += 4096;

				// Get progress percentage from the read bytes and total length
				$progress = min(100, 100 * $readBytes / $contentLength);
				// Print progress to the UI using a JSON array
				$response = ['stage' => 0, 'progress' => $progress];
				echo json_encode($response);
			}
			// Close the handles of both files
			fclose($remote);
			fclose($local);
		}
		else{
			error_log("Content length was 0 for URL: " . $url);
			throw new \Exception("Downloaded audio length was 0, please try again later");
		}
		$this->applyArt();

		return true;
	}

	/**
	 * Since SoundCloud is audio only, we do not convert, but only add the album artwork
	 */
	public function convert(){

	}

	public function applyArt(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$ffmpegAlbumArt = $path . $this->video->getThumbnailFilename();
		$ffmpegOutFile = $path . $this->video->getFilename() . $this->video->getFileExtension();
		$ffmpegTempFile = $path . $this->video->getFilename() . "-art.mp3";

		exec("ffmpeg -i \"$ffmpegOutFile\" -i \"$ffmpegAlbumArt\" -y -acodec copy -map 0 -map 1 -id3v2_version 3 -metadata:s:v title=\"Album cover\" -metadata:s:v comment=\"Cover (Front)\"  \"$ffmpegTempFile\"");
		rename($ffmpegTempFile, $ffmpegOutFile);

		$this->video->setDuration(SupportedSite::getDurationSeconds($ffmpegOutFile));

		// Send progress to UI
		$response = ['stage' => 1, 'progress' => 100];
		echo json_encode($response);

		return;
	}
}
