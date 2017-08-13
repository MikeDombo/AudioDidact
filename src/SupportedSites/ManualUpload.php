<?php

namespace AudioDidact\SupportedSites;

use AudioDidact\Video;

class ManualUpload extends SupportedSite {

	public function __construct($data, $isVideo){
		$this->video = new Video();

		// If there is a URL/ID, continue
		if($data != null){
			$this->video->setURL($data["filename"]);
			$this->video->setIsVideo($isVideo);

			// Set video ID and time to current time
			$this->video->setId($data["ID"]);
			$this->video->setFilename($this->video->getId());
			$this->video->setThumbnailFilename($data["thumbnailFilename"]);
			$this->video->setTime(time());
			$this->video->setDuration($data["duration"]);
			$this->video->setTitle($data["title"]);
			$this->video->setAuthor($data["author"]);
			$this->video->setDesc($data["description"]);
		}
	}

	/**
	 * Checks if all thumbnail, video, and mp3 are downloaded and have a length (ie. video or audio are not null)
	 *
	 * @return bool
	 */
	public function allDownloaded(){
		$downloadPath = DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$downloadFilePath = $downloadPath . $this->video->getFilename();

		$p = pathinfo($this->video->getURL())["extension"];
		// If the thumbnail has not been downloaded, go ahead and download it
		if(!file_exists($this->video->getThumbnailFilename())){
			$this->downloadThumbnail();
		}
		// If the mp3 and mp4 files exist, check if the mp3 has a duration that is not null
		if(file_exists($downloadFilePath . ".mp3") && SupportedSite::getDuration($downloadFilePath . ".mp3")){
			if($p == "mp3"){
				// Before returning true, set the duration since convert will not be run
				$this->video->setDuration(SupportedSite::getDurationSeconds($downloadFilePath . ".mp3"));

				return true;
			}
			else if(file_exists($downloadFilePath . ".mp4") && SupportedSite::getDuration($downloadFilePath . ".mp4") ==
				SupportedSite::getDuration($downloadFilePath . ".mp3")
			){
				// Before returning true, set the duration since convert will not be run
				$this->video->setDuration(SupportedSite::getDurationSeconds($downloadFilePath . ".mp3"));

				return true;
			}
		}
		// If only the mp4 is downloaded (and has a duration) or the mp3 duration is null, then convert the mp4 to mp3
		if(!$this->video->isIsVideo() && $p != "mp3" && file_exists($downloadFilePath . ".mp4") &&
			SupportedSite::getDuration($downloadFilePath . ".mp4")
		){
			$this->convert();
			$this->applyArt();

			return true;
		}

		// If all else fails, return false
		return false;
	}

	public function downloadThumbnail(){
		return;
	}

	public function downloadVideo(){
		return;
	}

	public function convert(){
		$p = pathinfo($this->video->getURL())["extension"];
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$ffmpegOutFile = $path . $this->video->getFilename() . ".mp3";
		if($p != "mp3"){
			parent::convert();

			return;
		}

		$this->video->setDuration(SupportedSite::getDurationSeconds($ffmpegOutFile));

		return;
	}
}
