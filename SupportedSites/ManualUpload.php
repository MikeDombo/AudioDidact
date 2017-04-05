<?php

class ManualUpload extends SupportedSite{

	public function __construct($data, $isVideo, PodTube $podtube){
		parent::$podtube = $podtube;
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

			// Check if the video already exists in the DB. If it does, then we do not need to get the information again
			if(!parent::$podtube->isInFeed($this->video->getId())){
				$this->video->setTitle($data["title"]);
				$this->video->setAuthor($data["author"]);
				$this->video->setDesc($data["description"]);
			}
			else{
				$this->video = parent::$podtube->getDataFromFeed($this->video->getId());
			}
		}
	}

	/**
	 * Checks if all thumbnail, video, and mp3 are downloaded and have a length (ie. video or audio are not null)
	 *
	 * @return bool
	 */
	public function allDownloaded(){
		$downloadPath = DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$downloadFilePath = $downloadPath.$this->video->getFilename();

		$p = pathinfo($this->video->getURL())["extension"];
		// If the thumbnail has not been downloaded, go ahead and download it
		if(!file_exists($this->video->getThumbnailFilename())){
			$this->downloadThumbnail();
		}
		// If the mp3 and mp4 files exist, check if the mp3 has a duration that is not null
		if(file_exists($downloadFilePath.".mp3") && SupportedSite::getDuration($downloadFilePath.".mp3")){
			if($p == "mp3"){
				// Before returning true, set the duration since convert will not be run
				$this->video->setDuration(SupportedSite::getDurationSeconds($downloadFilePath.".mp3"));
				return true;
			}
			else if(file_exists($downloadFilePath.".mp4") && SupportedSite::getDuration($downloadFilePath.".mp4") ==
				SupportedSite::getDuration($downloadFilePath.".mp3")){
				// Before returning true, set the duration since convert will not be run
				$this->video->setDuration(SupportedSite::getDurationSeconds($downloadFilePath.".mp3"));
				return true;
			}
		}
		// If only the mp4 is downloaded (and has a duration) or the mp3 duration is null, then convert the mp4 to mp3
		if(!$this->video->isIsVideo() && $p != "mp3" && file_exists($downloadFilePath.".mp4") &&
			SupportedSite::getDuration($downloadFilePath.".mp4")){
			$this->convert();
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

	private function applyArt(){
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$ffmpeg_outfile = $path.$this->video->getFilename().".mp3";
		$ffmpeg_albumArt = $path.$this->video->getThumbnailFilename();
		$ffmpeg_tempFile = $path.$this->video->getFilename()."-art.mp3";
		exec("ffmpeg -i \"$ffmpeg_outfile\" -i \"$ffmpeg_albumArt\" -y -c copy -map 0 -map 1 -id3v2_version 3 -metadata:s:v title=\"Album cover\" -metadata:s:v comment=\"Cover (Front)\"  \"$ffmpeg_tempFile\"");
		rename($ffmpeg_tempFile, $ffmpeg_outfile);
	}

	public function convert(){
		$p = pathinfo($this->video->getURL())["extension"];
		$path = getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR;
		$ffmpeg_infile = $path.$this->video->getFilename().".mp4";
		$ffmpeg_outfile = $path.$this->video->getFilename().".mp3";
		if($p != "mp3"){
			// Use ffmpeg to convert the audio in the background and save output to a file called videoID.txt
			$cmd = "ffmpeg -i \"$ffmpeg_infile\" -y -q:a 5 -map a \"$ffmpeg_outfile\" 1> ".$this->video->getID().".txt 2>&1";

			// Check if we're on Windows or *nix
			if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
				// Start the command in the background
				pclose(popen("start /B ".$cmd, "r"));
			}else{
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
				$response = array('stage' => 1, 'progress' => $progress);
				echo json_encode($response);
				usleep(500000);
			}
			// Delete the temporary file that contained the ffmpeg output
			@unlink($this->video->getID().".txt");
			return;
		}
		$this->video->setDuration(SupportedSite::getDurationSeconds($ffmpeg_outfile));
		$this->applyArt();
		return;
	}
}
