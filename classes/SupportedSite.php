<?php
namespace AudioDidact\SupportedSites;

abstract class SupportedSite {
	/** @var \AudioDidact\Video local video object */
	protected $video;

	/**
	 * Checks if all thumbnail, video, and mp3 are downloaded and have a length (ie. video or audio are not null)
	 * @return bool
	 */
	abstract public function allDownloaded();

	abstract public function downloadThumbnail();

	abstract public function downloadVideo();

	protected function echoErrorJSON($message){
		echo json_encode(['stage' =>-1, 'progress' => 0, 'error'=> $message]);
	}

	abstract public function convert();

	/**
	 * Returns the current Video object
	 * @return \AudioDidact\Video
	 */
	public function getVideo(){
		return $this->video;
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
	 * Get duration in seconds of media file from ffmpeg
	 * @param $file
	 * @return bool|string
	 */
	public static function getDurationSeconds($file){
		$dur = shell_exec("ffmpeg -i ".$file." 2>&1");
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
		return $seconds + ($minutes*60) + ($hours*60*60);
	}
}