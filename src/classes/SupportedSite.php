<?php

namespace AudioDidact\SupportedSites;

abstract class SupportedSite {
	/** @var \AudioDidact\Video local video object */
	protected $video;

	public static function supportsURL($url){
		return false;
	}

	/**
	 * Get duration of media file from ffmpeg
	 *
	 * @param $file
	 * @return bool|string
	 */
	public static function getDuration($file){
		$dur = shell_exec("ffmpeg -i " . $file . " 2>&1");
		if(preg_match("/: Invalid /", $dur)){
			return false;
		}
		preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $dur, $duration);
		if(!isset($duration[1])){
			return false;
		}

		return $duration[1] . ":" . $duration[2] . ":" . $duration[3];
	}

	/**
	 * Get duration in seconds of media file from ffmpeg
	 *
	 * @param $file
	 * @return bool|string
	 */
	public static function getDurationSeconds($file){
		$dur = shell_exec("ffmpeg -i " . $file . " 2>&1");
		if(preg_match("/: Invalid /", $dur)){
			return false;
		}
		preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $dur, $duration);
		if(!isset($duration[1])){
			return false;
		}
		$hours = $duration[1];
		$minutes = $duration[2];
		$seconds = $duration[3];

		return $seconds + ($minutes * 60) + ($hours * 60 * 60);
	}

	/**
	 * Checks if all thumbnail, video, and mp3 are downloaded and have a length (ie. video or audio are not null)
	 *
	 * @param \AudioDidact\Video $vid
	 * @return bool
	 */
	public static function allDownloadedVideo(\AudioDidact\Video $vid){
		$downloadPath = DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$downloadFilePath = $downloadPath . $vid->getFilename();
		$fullDownloadPath = $downloadFilePath . $vid->getFileExtension();

		if($vid->isIsVideo() && file_exists($fullDownloadPath) && SupportedSite::getDuration($fullDownloadPath)){
			// If only the mp4 is downloaded (and has a duration)
			$vid->setDuration(SupportedSite::getDurationSeconds($fullDownloadPath));

			return true;
		}
		else if(file_exists($downloadFilePath . ".mp3") &&
			file_exists($downloadFilePath . ".mp4") &&
			SupportedSite::getDuration($downloadFilePath . ".mp3") &&
			abs(SupportedSite::getDurationSeconds($downloadFilePath . ".mp4") - SupportedSite::getDurationSeconds
			($downloadFilePath . ".mp3")) <= 5
		){
			// Before returning true, set the duration since convert will not be run
			$vid->setDuration(SupportedSite::getDurationSeconds($fullDownloadPath));

			return true;
		}

		// If all else fails, return false
		return false;
	}

	public function allDownloaded(){
		// If the thumbnail has not been downloaded, go ahead and download it
		if(!file_exists(DOWNLOAD_PATH . DIRECTORY_SEPARATOR . $this->video->getThumbnailFilename())){
			$this->downloadThumbnail();
		}
		return static::allDownloadedVideo($this->video);
	}

	abstract public function downloadThumbnail();

	abstract public function downloadVideo();

	public function applyArt(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$ffmpegAlbumArt = $path . $this->video->getThumbnailFilename();
		$ffmpegOutFile = $path . $this->video->getFilename() . $this->video->getFileExtension();
		$ffmpegTempFile = $path . $this->video->getFilename() . "-art.mp3";

		exec("ffmpeg -i \"$ffmpegOutFile\" -i \"$ffmpegAlbumArt\" -y -c copy -map 0 -map 1 -id3v2_version 3 -metadata:s:v title=\"Album cover\" -metadata:s:v comment=\"Cover (Front)\"  \"$ffmpegTempFile\" 2>&1");
		rename($ffmpegTempFile, $ffmpegOutFile);

		$this->video->setDuration(SupportedSite::getDurationSeconds($ffmpegOutFile));

		return;
	}

	/**
	 * Converts mp4 video to mp3 audio using ffmpeg
	 */
	public function convert(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$ffmpegInFile = $path . $this->video->getFilename() . ".mp4";
		$ffmpegOutFile = $path . $this->video->getFilename() . $this->video->getFileExtension();

		// Use ffmpeg to convert the audio in the background and save output to a file called videoID.txt
		$cmd = "ffmpeg -i \"$ffmpegInFile\" -y -q:a 5 -map a \"$ffmpegOutFile\" > " . $this->video->getID() . ".txt 2>&1";

		// Check if we're on Windows or *nix
		if(strtoupper(mb_substr(PHP_OS, 0, 3)) === 'WIN'){
			// Start the command in the background
			pclose(popen("start /B " . $cmd, "r"));
		}
		else{
			pclose(popen($cmd . " &", "r"));
		}

		$progress = 0;
		// Get the conversion progress and output the progress to the UI using a JSON array
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
			$response = ['stage' => 1, 'progress' => $progress];
			echo json_encode($response);
			usleep(500000);
		}

		// Delete the temporary file that contained the ffmpeg output
		@unlink($this->video->getID() . ".txt");

		return $ffmpegOutFile;
	}

	/**
	 * Download the video to $localFile with a given $url
	 * While downloading output progress to UI as JSON array
	 *
	 * @param $url
	 * @param $localFile
	 * @return bool
	 * @throws \Exception
	 */
	protected function downloadWithPercentage($url, $localFile){
		// Use CURL to get the download content length in order to print the progress
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		if($data === false){
			throw new \Exception("Download failed, URL tried was " . $url . "\ncURL error (" . curl_errno($ch) . "): "
				. curl_error($ch));
		}
		curl_close($ch);

		// Get content length in bytes
		$contentLength = 'unknown';
		if(preg_match_all('/Content-Length: (\d+)/', $data, $matches)){
			$contentLength = (int)$matches[count($matches) - 1][count($matches[count($matches) - 1]) - 1];
		}

		if(intval($contentLength) > 0){
			// Open local and remote files for write and read respectively
			$remote = fopen($url, 'r');
			$local = fopen($localFile, 'w');

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
			throw new \Exception("Downloaded file length was 0, please try again later");
		}

		return true;
	}

	/**
	 * Returns the current Video object
	 *
	 * @return \AudioDidact\Video
	 */
	public function getVideo(){
		return $this->video;
	}

	public static function echoErrorJSON($message){
		echo json_encode(['stage' => -1, 'progress' => 0, 'error' => $message]);
	}
}
