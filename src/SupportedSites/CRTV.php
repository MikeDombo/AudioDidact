<?php

namespace AudioDidact\SupportedSites;

use AudioDidact\Video;

/**
 * Class CRTV
 */
class CRTV extends SupportedSite {
	// Setup global variables
	private $thumbnailURL;

	/**
	 * CRTV constructor. Gets the video information, checks for it in the user's feed.
	 *
	 * @param string $str
	 * @param boolean $downloadVideo
	 * @throws \Exception
	 */
	public function __construct($str, $downloadVideo){
		$this->video = new Video();

		// If there is a URL/ID, continue
		if($str != null){
			$this->video->setURL($str);
			$this->video->setIsVideo($downloadVideo);

			// Set video ID and time to current time
			$info = $this->getVideoInfo($str);
			$this->video->setId($info["ID"]);
			$this->video->setFilename($this->video->getId());
			$this->video->setThumbnailFilename($this->video->getFilename() . ".jpg");
			$this->video->setTime(time());
			$this->video->setTitle($info["title"]);
			$this->video->setAuthor("CRTV");
			$this->video->setDesc($info["description"]);
		}
	}

	/**
	 * @param $str
	 * @return array
	 * @throws \Exception
	 */
	private function getVideoInfo($str){
		// Download CRTV video page
		$crtvHTML = file_get_contents($str);
		$videoJSON = [];
		$vidFound = mb_eregi("var\s+video\s*=\s*({[^}]*});", $crtvHTML, $videoJSON);
		if($vidFound == false || count($videoJSON) != 2){
			throw new \Exception("Unable to find JSON for that video.");
		}

		$videoJSON = mb_eregi_replace("(\w+):\s*\"", "\"\\1\":\"", $videoJSON[1]);
		$videoInfo = json_decode($videoJSON, true);
		$this->thumbnailURL = $videoInfo["image"];

		return ["ID" => $videoInfo["id"], "title" => $videoInfo["name"], "description" => $videoInfo["description"]];
	}

	/**
	 * Download thumbnail using videoID from Brightcove
	 */
	public function downloadThumbnail(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$thumbnail = $path . $this->video->getThumbnailFilename();
		file_put_contents($thumbnail, fopen($this->thumbnailURL, "r"));
		// Set the thumbnail file as publicly accessible
		@chmod($thumbnail, 0775);
	}

	public function downloadVideo(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;

		$videoFilename = $this->video->getFilename() . ".mp4";
		$videoPath = $path . $videoFilename;

		$m3u8URL = $this->getM3U8Playlist($this->video->getId());
		$cmd = "ffmpeg -i \"" . $m3u8URL . "\" -map 0:p:0 -y -c copy -bsf:a aac_adtstoasc \"" . $videoPath . "\" > "
			. $this->video->getID() . ".txt 2>&1";

		// Check if we're on Windows or *nix
		if(strtoupper(mb_substr(PHP_OS, 0, 3)) === 'WIN'){
			// Start the command in the background
			pclose(popen("start /B " . $cmd, "r"));
		}
		else{
			pclose(popen($cmd . " &", "r"));
		}

		$progress = 0;
		// Get the download and conversion progress and output the progress to the UI using a JSON array
		while($progress != 100){
			$content = @file_get_contents($this->video->getID() . '.txt');
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
			if(!empty($ar[1])){
				$duration += intval($ar[1]) * 60;
			}
			if(!empty($ar[2])){
				$duration += intval($ar[2]) * 60 * 60;
			}
			preg_match_all("/time=(.*?) bitrate/", $content, $matches);

			// Matches time of the converted file and gets the percentage complete
			$rawTime = array_pop($matches);
			if(is_array($rawTime)){
				$rawTime = array_pop($rawTime);
			}
			$ar = array_reverse(explode(":", $rawTime));
			$time = floatval($ar[0]);
			if(!empty($ar[1])){
				$time += intval($ar[1]) * 60;
			}
			if(!empty($ar[2])){
				$time += intval($ar[2]) * 60 * 60;
			}
			$progress = round(($time / $duration) * 100);

			// Send progress to UI
			$response = ['stage' => 0, 'progress' => $progress];
			echo json_encode($response);
			usleep(500000);
		}
		// Delete the temporary file that contained the ffmpeg output
		@unlink($this->video->getID() . ".txt");

		$this->video->setDuration(SupportedSite::getDurationSeconds($videoPath));
	}

	public static function supportsURL($url){
		return mb_strpos($url, "crtv.com") > -1;
	}

	private function getM3U8Playlist($getId){
		$ch = curl_init();

		$formParams = ["id" => $getId, "format" => "json", "type" => "video"];
		curl_setopt_array($ch, [
			CURLOPT_URL => "https://www.crtv.com/service/publishpoint",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query($formParams),
			CURLOPT_HTTPHEADER => [
				"cache-control: no-cache",
				"content-type: application/x-www-form-urlencoded",
				"origin: https://neulionms-a.akamaihd.net",
				"cookie: nllinktoken=" . SUPPORTED_SITES_CRTV
			],
		]);

		$response = curl_exec($ch);
		curl_close($ch);

		return json_decode($response, true)["path"];
	}
}
