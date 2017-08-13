<?php

namespace AudioDidact\SupportedSites;

use AudioDidact\Video;

/**
 * Class CRTV
 */
class CRTV extends SupportedSite {
	// Setup global variables
	/** @var string YouTube URL */
	private $brightcoveBaseURL = "https://secure.brightcove.com/services/mobile/streaming/index/master.m3u8";
	private $pubId;
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
			$d = $this->getVideoId($str);
			$this->video->setId($d["ID"]);
			$this->video->setFilename($this->video->getId());
			$this->video->setThumbnailFilename($this->video->getFilename() . ".jpg");
			$this->video->setTime(time());
			$this->getPublisherID($d["html"]);
			$info = $this->getVideoInfo($d["html"]);
			$this->video->setTitle($info["title"]);
			$this->video->setAuthor("CRTV");
			$this->video->setDesc($info["description"]);
		}
	}

	private function getVideoId($str){
		// Download CRTV video page
		$crtvHTML = file_get_contents($str);

		// Setup CRTV webpage parsing objects
		$doc = new \DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($crtvHTML);
		$finder = new \DomXPath($doc);

		// Get Thumbnail
		$tbURL = "";
		$nodes = $finder->query("//meta[@property='og:image']");
		foreach($nodes as $i){
			$tbURL = $i->getAttribute('content');
		}
		parse_str(parse_url($tbURL, PHP_URL_QUERY), $query);
		$videoId = $query["videoId"];

		$this->thumbnailURL = $tbURL;

		return ["ID" => $videoId, "html" => $crtvHTML];
	}

	private function getPublisherID($html){
		// Get Brightcove Publisher/Account ID
		preg_match('/dataAc{1,2}ountId:\s+[\'\"](\d+)[\'\"]/', $html, $matches);
		$this->pubId = $matches[1];
	}

	private function getVideoInfo($html){
		// Get Video Title
		preg_match('/\<title\>([^\<]*)\<\/title\>/', $html, $matches);
		$videoTitle = $matches[1];
		$videoTitle = html_entity_decode($videoTitle, ENT_QUOTES, 'UTF-8');

		// Get video description
		preg_match('/\<div class=[\"\']rtf[\'\"]\>\s*\<p\>\s*(.*)\s*\<\/p\>\s*\<\/div\>/', $html, $matches);
		$desc = $matches[1];
		$desc = html_entity_decode($desc, ENT_QUOTES, 'UTF-8');

		return ["title" => $videoTitle, "description" => $desc];
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

		// Based on gathered information, generate Brightcove query parameters to get the HLS playlist
		$m3u8URL = $this->brightcoveBaseURL . "?videoId=" . $this->video->getId() . "&pubId=" . $this->pubId . "&secure=true";
		$cmd = "ffmpeg -i \"" . $m3u8URL . "\" -map 0:p:1 -y -c copy -bsf:a aac_adtstoasc \"" . $videoPath . "\" 1> "
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
}
