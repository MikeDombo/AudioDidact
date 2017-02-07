<?php


abstract class SupportedSite {
	/** @var  \PodTube static \PodTube object */
	protected static $podtube;
	/** @var \Video local video object */
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
	 * @return Video
	 */
	public function getVideo(){
		return $this->video;
	}
}