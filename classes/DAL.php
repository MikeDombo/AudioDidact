<?php


/**
 * Class DAL
 */
abstract class DAL {
	protected static $PDO;

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

	/**
	 * @param \User $user
	 * @return mixed
	 */
	abstract public function updateUser(User $user);


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
	 * @param $webID
	 * @return bool
	 */
	public function webIDExists($webID){
		if($this->getUserByWebID($webID) != null){
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
	abstract public function makeDB();

	/**
	 * @return mixed
	 */
	abstract public function verifyDB();
	
	abstract public function getPrunableVideos();
}
