<?php
spl_autoload_register(function($class){
	require_once __DIR__.'/classes/Video.php';
});
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");

abstract class DAL {
	static protected $PDO;

	abstract public function getUserByUsername($username);
	abstract public function getUserByEmail($email);
	abstract public function getUserByID($id);
	abstract public function getFeed(User $user);
	abstract public function getVideoByID(User $user, $id);
	abstract public function addUser(User $user);
	abstract public function addVideo(Video $vid, User $user);

	// Default slow way to check if a video is in the feed. Override for faster lookup
	public function inFeed(Video $vid, User $user){
		$f = $this->getFeed($user);
		foreach($f as $v){
			if($v->getId() == $vid->getId()){
				return true;
			}
		}
		return false;
	}
	public function usernameExists($username){
		if($this->getUserByUsername($username) != null){
			return true;
		}
	}
	public function emailExists($email){
		if($this->getUserByEmail($email) != null){
			return true;
		}
	}

	abstract protected function makeDB();
	abstract protected function verifyDB();
}
?>