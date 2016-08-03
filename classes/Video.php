<?php
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");

/**
 * Class Video
 */
class Video{
	private $id;
	private $title;
	private $desc;
	private $time;
	private $duration;
	private $author;
	private $order;

	/**
	 * @return mixed
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getTitle(){
		return $this->title;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title){
		$this->title = $title;
	}

	/**
	 * @return mixed
	 */
	public function getDesc(){
		return $this->desc;
	}

	/**
	 * @param mixed $desc
	 */
	public function setDesc($desc){
		$this->desc = $desc;
	}

	/**
	 * @return mixed
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 * @param mixed $time
	 */
	public function setTime($time){
		$this->time = $time;
	}

	/**
	 * @return mixed
	 */
	public function getDuration(){
		return $this->duration;
	}

	/**
	 * @param mixed $duration
	 */
	public function setDuration($duration){
		$this->duration = $duration;
	}

	/**
	 * @return mixed
	 */
	public function getAuthor(){
		return $this->author;
	}

	/**
	 * @param mixed $author
	 */
	public function setAuthor($author){
		$this->author = $author;
	}

	/**
	 * @return mixed
	 */
	public function getOrder(){
		return $this->order;
	}

	/**
	 * @param mixed $order
	 */
	public function setOrder($order){
		$this->order = $order;
	}


}
?>