<?php
spl_autoload_register(function($class){
	require_once __DIR__.'/Video.php';
	require_once __DIR__.'/User.php';
});
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");

/**
 * Class DAL
 */
abstract class DAL {
	static protected $PDO;

	/**
	 *
	 * @param $username
	 * @return mixed
	 */
	abstract public function getUserByUsername($username);

	/**
	 * @param $email
	 * @return mixed
	 */
	abstract public function getUserByEmail($email);

	/**
	 * @param $id
	 * @return mixed
	 */
	abstract public function getUserByID($id);

	/**
	 * @param $webID
	 * @return mixed
	 */
	abstract public function getUserByWebID($webID);

	/**
	 * @param \User $user
	 * @return mixed
	 */
	abstract public function getFeed(User $user);

	/**
	 * @param \User $user
	 * @return mixed
	 */
	abstract public function getFeedText(User $user);

	/**
	 * @param \User $user
	 * @param $id
	 * @return mixed
	 */
	abstract public function getVideoByID(User $user, $id);

	/**
	 * @param \User $user
	 * @return mixed
	 */
	abstract public function addUser(User $user);

	/**
	 * @param \Video $vid
	 * @param \User $user
	 * @return mixed
	 */
	abstract public function addVideo(Video $vid, User $user);

	/**
	 * @param \User $user
	 * @param $feed
	 * @return mixed
	 */
	abstract public function setFeedText(User $user, $feed);


	/** Default slow way to check if a video is in the feed. Override for faster lookup
	 * @param \Video $vid
	 * @param \User $user
	 * @return bool
	 */
	public function inFeed(Video $vid, User $user){
		$f = $this->getFeed($user);
		foreach($f as $v){
			if($v->getId() == $vid->getId()){
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $username
	 * @return bool
	 */
	public function usernameExists($username){
		$username = strtolower($username);
		if($this->getUserByUsername($username) != null){
			return true;
		}
		return false;
	}

	/**
	 * @param $email
	 * @return bool
	 */
	public function emailExists($email){
		$email = strtolower($email);
		if($this->getUserByEmail($email) != null){
			return true;
		}
		return false;
	}

	/**
	 * @return mixed
	 */
	abstract protected function makeDB();

	/**
	 * @return mixed
	 */
	abstract protected function verifyDB();
}
?>