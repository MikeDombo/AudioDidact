<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 7/23/2017
 * Time: 11:39 AM
 */

namespace AudioDidact\SupportedSites;
use AudioDidact\Video;

class Vimeo extends SupportedSite {
	// Setup global variables
	/** @var string Vimeo URL */
	private $vimeoConfigBaseURL = "https://player.vimeo.com/video/";
	private $vimeoBaseURL = "https://vimeo.com/";
	private $downloadURL = "";
	private $thumbnailURL = "";

	/**
	 * YouTube constructor. Gets the video information, checks for it in the user's feed.
	 *
	 * @param string $str
	 * @param boolean $isVideo
	 * @throws \Exception
	 */
	public function __construct($str, $isVideo){
		$this->video = new Video();

		// If there is a URL/ID, continue
		if($str != null){
			$this->video->setURL($str);
			$this->video->setIsVideo($isVideo);

			// Set video ID from setYoutubeID and time to current time
			$this->video->setId($this->setVimeoID($str));
			$this->video->setFilename($this->video->getId());
			$this->video->setThumbnailFilename($this->video->getFilename() . ".jpg");
			$this->video->setTime(time());

			$info = $this->getInfo();
			$this->video->setTitle($info["title"]);
			$this->video->setAuthor($info["author"]);
			$this->video->setDesc($info["description"]);
		}
	}

	private function getInfo(){
		$configJSON = file_get_contents($this->vimeoConfigBaseURL.$this->video->getId()."/config");
		$config = json_decode($configJSON, true);
		if($config == false){
			throw new \Exception("Unable to parse vimeo JSON");
		}
		$downloadURLs = $config["request"]["files"]["progressive"];
		$this->downloadURL = $downloadURLs[count($downloadURLs) - 1]["url"];
		$this->thumbnailURL = $config["video"]["thumbs"]["640"];

		$info = [];
		$info["author"] = $config["video"]["owner"]["name"];
		$info["title"] = $config["video"]["title"];

		$videoHTML = file_get_contents($this->vimeoBaseURL.$this->video->getId());
		$doc = new \DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($videoHTML);
		$finder = new \DomXPath($doc);
		$info["description"] = "";
		$nodes = $finder->query("//div[@class='clip_details-description description-wrapper iris_desc']");
		foreach($nodes as $i){
			/** @var \DOMElement $i */
			$info["description"] = trim($i->textContent);
		}

		return $info;
	}

	/**
	 * Set YouTube ID from a given string using parseYoutubeURL
	 *
	 * @param string $str
	 * @return bool
	 * @throws \Exception
	 */
	private function setVimeoID($str){
		$vidId = [];
		// Try and parse the string into a usable ID
		$tmpId = mb_ereg("vimeo\.com\/(\d{9})", $str, $vidId);
		if($tmpId > 0 && count($vidId) == 2){
			return $vidId[1];
		}
		error_log("unable to parse vimeo URL ".$str);
		throw new \Exception("Could not parse that vimeo URL");
	}

	/**
	 * Download thumbnail using videoID from YouTube's image server
	 */
	public function downloadThumbnail(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$thumbnail = $path . $this->video->getThumbnailFilename();
		file_put_contents($thumbnail, fopen($this->thumbnailURL, "r"));
		// Set the thumbnail file as publicly accessible
		@chmod($thumbnail, 0775);
	}

	/**
	 * Download video using download URL from Python script and then call downloadWithPercentage to actually download
	 * the video
	 */
	public function downloadVideo(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$videoFilename = $this->video->getFilename() . ".mp4";
		$videoPath = $path . $videoFilename;

		$url = $this->downloadURL;
		if(mb_strpos($url, "Error:") > -1){
			error_log("$url");
			throw new \Exception($url);
		}
		try{
			/* Actually download the video from the url and print the
			 * percentage to the screen with JSON
			 */
			$this->downloadWithPercentage($url, $videoPath);
			// Set the video file as publicly accessible
			@chmod($videoPath, 0775);
			$this->video->setDuration(SupportedSite::getDurationSeconds($videoPath));
		}
		catch(\Exception $e){
			throw $e;
		}
	}
}
