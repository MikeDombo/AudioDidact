<?php
namespace AudioDidact;

/**
 * Class DAL
 */
abstract class DAL {
	/** @var  \PDO static \PDO object */
	protected static $PDO;

	/**
	 * Returns User class built from the database
	 * @param string $username
	 * @return User
	 */
	abstract public function getUserByUsername($username);

	/**
	 * Returns User class built from the database
	 * @param string $email
	 * @return User
	 */
	abstract public function getUserByEmail($email);

	/**
	 * Returns User class built from the database
	 * @param int $id
	 * @return User
	 */
	abstract public function getUserByID($id);

	/**
	 * Returns User class built from the database
	 * @param string $webID
	 * @return User
	 */
	abstract public function getUserByWebID($webID);

	/**
	 * Gets all the videos from the database in the user's current feed
	 * limited by the max number of items the user has set
	 * @param User $user
	 * @return mixed
	 */
	abstract public function getFeed(User $user);

	/**
	 * Gets all the videos from the database
	 * @param User $user
	 * @return mixed
	 */
	abstract public function getFullFeedHistory(User $user);

	/**
	 * Gets the full text of the feed from the database
	 * @param User $user
	 * @return string
	 */
	abstract public function getFeedText(User $user);

	/**
	 * Puts user into the database
	 * @param User $user
	 * @return void
	 */
	abstract public function addUser(User $user);

	/**
	 * Adds video into the video database for a specific user
	 * @param Video $vid
	 * @param User $user
	 * @return mixed
	 */
	abstract public function addVideo(Video $vid, User $user);

	/**
	 * Updates an existing video in the video database for a specific user
	 * @param Video $vid
	 * @param User $user
	 * @return mixed
	 */
	abstract public function updateVideo(Video $vid, User $user);

	/**
	 * Sets feed xml text for a user
	 * @param User $user
	 * @param $feed
	 * @return mixed
	 */
	abstract public function setFeedText(User $user, $feed);

	/**
	 * Updates user entry in the database
	 * @param User $user
	 */
	abstract public function updateUser(User $user);

	/**
	 * Updates only a user's password in the database
	 * @param User $user
	 */
	abstract public function updateUserPassword(User $user);


	/**
	 * Updates only a user's email verification and password recovery codes in the database
	 * @param User $user
	 */
	abstract public function updateUserEmailPasswordCodes(User $user);

	/**
	 * Default slow way to check if a video is in the feed. Override for faster lookup
	 * @param Video $vid
	 * @param User $user
	 * @return bool
	 */
	public function inFeed(Video $vid, User $user){
		$f = $this->getFeed($user);
		if($f == null){
			return false;
		}
		foreach($f as $v){
			/** @var $v \AudioDidact\Video */
			if($v->getId() == $vid->getId()){
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks if a username is taken in the database
	 * @param string $username
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
	 * Checks if a webID is taken in the database
	 * @param string $webID
	 * @return bool
	 */
	public function webIDExists($webID){
		if($this->getUserByWebID($webID) != null){
			return true;
		}
		return false;
	}

	/**
	 * Checks if an email is already in the database
	 * @param string $email
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
	 * Sets up any database necessary
	 * @param int $code
	 * @return mixed
	 */
	abstract public function makeDB($code);

	/**
	 * Verifies the database
	 * @return mixed
	 */
	abstract public function verifyDB();

	/**
	 * Returns an array of video IDs that can be safely deleted
	 * @return mixed
	 */
	abstract public function getPrunableVideos();
}
