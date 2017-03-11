<?php
require_once __DIR__."/../header.php";

/**
 * Class Video
 * Stores YouTube video specific data
 */
class Video{
	/** @var string YouTube video ID */
	private $id;
	/** @var string video title */
	private $title;
	/** @var string video description (UTF-8) */
	private $desc;
	/** @var string date and time video was added to the feed */
	private $time;
	/** @var int the duration in seconds of the video */
	private $duration;
	/** @var string video author or channel */
	private $author;
	/** @var int order of this video in the feed */
	private $order;
	/** @var string url of video page */
	private $url;

	/**
	 * Gets the YouTube video ID
	 * @return mixed
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Sets the YouTube video ID
	 * @param mixed $id
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	 * Gets the video title
	 * @return mixed
	 */
	public function getTitle(){
		return $this->title;
	}

	/**
	 * Sets the video title
	 * @param mixed $title
	 */
	public function setTitle($title){
		$this->title = $title;
	}

	/**
	 * Gets the video description
	 * @return mixed
	 */
	public function getDesc(){
		return $this->desc;
	}

	/**
	 * Sets the video description
	 * @param mixed $desc
	 */
	public function setDesc($desc){
		$this->desc = $desc;
	}

	/**
	 * Gets the time the video was added
	 * @return mixed
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 * Sets the time the video was added
	 * @param mixed $time
	 */
	public function setTime($time){
		$this->time = $time;
	}

	/**
	 * Gets the duration of the video in seconds
	 * @return mixed
	 */
	public function getDuration(){
		if($this->duration == null || $this->duration == 0){
			return YouTube::getDurationSeconds(getcwd().DIRECTORY_SEPARATOR.DOWNLOAD_PATH.DIRECTORY_SEPARATOR
				.$this->getId().".mp3");
		}
		return $this->duration;
	}

	/**
	 * Sets the duration of the video in seconds
	 * @param mixed $duration
	 */
	public function setDuration($duration){
		$this->duration = $duration;
	}

	/**
	 * Gets the video author
	 * @return mixed
	 */
	public function getAuthor(){
		return $this->author;
	}

	/**
	 * Sets the video author
	 * @param mixed $author
	 */
	public function setAuthor($author){
		$this->author = $author;
	}

	/**
	 * Gets the order of the video in the feed
	 * @return int
	 */
	public function getOrder(){
		return $this->order;
	}

	/**
	 * Sets the order of the video in the feed
	 * @param int $order
	 */
	public function setOrder($order){
		$this->order = $order;
	}

	/**
	 * @return string
	 */
	public function getURL(){
		return $this->url;
	}

	/**
	 * @param string $url
	 */
	public function setURL($url){
		$this->url = $url;
	}
}
