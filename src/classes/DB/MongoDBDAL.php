<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 8/8/2017
 * Time: 8:08 PM
 */

namespace AudioDidact\DB;

use AudioDidact\User;
use AudioDidact\Video;
use MongoDB\Client;

class MongoDBDAL extends DAL {
	private $feeds;
	private $users;

	/**
	 * MongoDBDAL constructor.
	 *
	 * @param $connectionString
	 */
	public function __construct($connectionString){
		$client = new Client($connectionString);
		$db = $client->selectDatabase(DB_DATABASE);
		$this->feeds = $db->selectCollection("feeds");
		$this->users = $db->selectCollection("users");
	}

	/**
	 * Returns User class built from the database
	 *
	 * @param int $id
	 * @return User
	 */
	public function getUserByID($id){
		$data = $this->users->findOne(["_id" => $id]);

		return $this->setUser($data);
	}

	/**
	 * Makes a new user object from a database select command.
	 *
	 * @param $row array Database row retrieved from another method
	 * @return User
	 */
	private function setUser($row){
		if($row == null){
			return null;
		}
		$user = new User();
		$user->setUserID($row["_id"]);
		$user->setUsername($row["username"]);
		$user->setPasswdDB($row["password"]);
		$user->setEmail($row["email"]);
		$user->setFname($row["firstname"]);
		$user->setLname($row["lastname"]);
		$user->setGender($row["gender"]);
		$user->setWebID($row["webID"]);
		$user->setFeedText($row["feedText"]);
		$user->setFeedLength($row["feedLength"]);
		$user->setFeedDetails($row["feedDetails"]);
		$user->setPrivateFeed($row["privateFeed"]);
		$user->setEmailVerificationCodes($row["emailVerificationCodes"]);
		$user->setPasswordRecoveryCodes($row["passwordRecoveryCodes"]);
		$user->setEmailVerified($row["emailVerified"]);

		return $user;
	}

	/**
	 * Puts user into the database
	 *
	 * @param User $user
	 * @return void
	 * @throws \Exception
	 */
	public function addUser(User $user){
		if(!$this->usernameExists($user->getUsername()) && !$this->emailExists($user->getEmail())){
			$result = $this->users->insertOne($this->makeUserArrayMongo($user));
			$user->setUserID($result->getInsertedId());
		}
		else{
			throw new \Exception("Username or Email Address Already Exists!");
		}
	}

	private function makeUserArrayMongo(User $user){
		return [
			"username" => $user->getUsername(),
			"password" => $user->getPasswd(),
			"email" => $user->getEmail(),
			"firstname" => $user->getFname(),
			"lastname" => $user->getLname(),
			"gender" => $user->getGender(),
			"webID" => $user->getWebID(),
			"feedText" => $user->getFeedText(),
			"feedLength" => $user->getFeedLength(),
			"feedDetails" => $user->getFeedDetails(),
			"privateFeed" => $user->isPrivateFeed(),
			"emailVerified" => $user->isEmailVerified(),
			"emailVerificationCodes" => $user->getEmailVerificationCodes(),
			"passwordRecoveryCodes" => $user->getPasswordRecoveryCodes()
		];
	}

	/**
	 * Adds video into the video database for a specific user
	 *
	 * @param Video $vid
	 * @param User $user
	 * @return mixed
	 */
	public function addVideo(Video $vid, User $user){
		$result = $this->feeds->findOne(["userID" => $user->getUserID()], ["projection" => ["orderID" => 1], "sort"
		=> ["orderID" => -1]]);
		if($result == null){
			$order = 1;
		}
		else{
			$order = intval($result["orderID"]) + 1;
		}

		$vid->setOrder($order);
		$vid->setTime(time());
		$this->feeds->insertOne($this->makeVideoArrayMongo($vid, $user));

		return true;
	}

	private function makeVideoArrayMongo(Video $vid, User $user){
		return [
			"userID" => $user->getUserID(),
			"videoID" => $vid->getId(),
			"url" => $vid->getURL(),
			"videoAuthor" => $vid->getAuthor(),
			"description" => $vid->getDesc(),
			"timeAdded" => $vid->getTime(),
			"videoTitle" => $vid->getTitle(),
			"duration" => $vid->getDuration(),
			"orderID" => $vid->getOrder(),
			"filename" => $vid->getFilename(),
			"thumbnailFilename" => $vid->getThumbnailFilename(),
			"isVideo" => $vid->isIsVideo()
		];
	}

	/**
	 * Updates an existing video in the video database for a specific user
	 *
	 * @param Video $vid
	 * @param User $user
	 * @return mixed
	 */
	public function updateVideo(Video $vid, User $user){
		$this->feeds->findOneAndReplace(["userID" => $user->getUserID(), "videoID" => $vid->getId(), "orderID" =>
			$vid->getOrder()], $this->makeVideoArrayMongo($vid, $user));

		return true;
	}

	/**
	 * Sets feed xml text for a user
	 *
	 * @param User $user
	 * @param $feed
	 * @return mixed
	 */
	public function setFeedText(User $user, $feed){
		$this->users->findOneAndUpdate(["_id" => $user->getUserID()], ['$set' => ["feedText" => $feed]]);

		return true;
	}

	/**
	 * Updates user entry in the database
	 *
	 * @param User $user
	 */
	public function updateUser(User $user){
		$this->users->findOneAndReplace(["_id" => $user->getUserID()], $this->makeUserArrayMongo($user));
	}

	/**
	 * Updates only a user's password in the database
	 *
	 * @param User $user
	 */
	public function updateUserPassword(User $user){
		$this->users->findOneAndUpdate(["_id" => $user->getUserID()], ['$set' => ["password" => $user->getPasswd()]]);
	}

	/**
	 * Updates only a user's email verification and password recovery codes in the database
	 *
	 * @param User $user
	 */
	public function updateUserEmailPasswordCodes(User $user){
		$this->users->findOneAndUpdate(["_id" => $user->getUserID()], ['$set' => ["emailVerificationCodes" =>
			$user->getEmailVerificationCodes(), "passwordRecoveryCodes" => $user->getPasswordRecoveryCodes()]]);
	}

	/**
	 * Gets all the videos from the database in the user's current feed
	 * limited by the max number of items the user has set
	 *
	 * @param User $user
	 * @return mixed
	 */
	public function getFeed(User $user){
		$rows = $this->feeds->find(["userID" => $user->getUserID()], ["sort" => ["orderID" => -1], "limit" => intval
		($user->getFeedLength())]);
		if($rows == null){
			return null;
		}

		$returner = [];
		foreach($rows as $row){
			$returner[] = $this->setVideo($row);
		}

		return $returner;
	}

	/**
	 * Makes a new video object from a database select command.
	 *
	 * @param $row array Database rows retrieved from another method
	 * @return Video
	 */
	private function setVideo($row){
		$vid = new Video();

		$vid->setAuthor($row["videoAuthor"]);
		$vid->setDesc($row["description"]);
		$vid->setId($row["videoID"]);
		$vid->setTime($row["timeAdded"]);
		$vid->setDuration(intval($row["duration"]));
		$vid->setTitle($row["videoTitle"]);
		$vid->setOrder($row["orderID"]);
		$vid->setURL($row["url"]);
		$vid->setIsVideo($row["isVideo"]);
		$vid->setFilename($row["filename"]);
		$vid->setThumbnailFilename($row["thumbnailFilename"]);

		return $vid;
	}

	/**
	 * Gets all the videos from the database
	 *
	 * @param User $user
	 * @return mixed
	 */
	public function getFullFeedHistory(User $user){
		$rows = $this->feeds->find(["userID" => $user->getUserID()], ["sort" => ["orderID" => -1]]);
		if($rows == null){
			return null;
		}

		$returner = [];
		foreach($rows as $row){
			$returner[] = $this->setVideo($row);
		}

		return $returner;
	}

	/**
	 * Returns User class built from the database
	 *
	 * @param string $username
	 * @return User
	 */
	public function getUserByUsername($username){
		$data = $this->users->findOne(["username" => $username]);

		return $this->setUser($data);
	}

	/**
	 * Returns User class built from the database
	 *
	 * @param string $webID
	 * @return User
	 */
	public function getUserByWebID($webID){
		$data = $this->users->findOne(["webID" => $webID]);

		return $this->setUser($data);
	}

	/**
	 * Returns User class built from the database
	 *
	 * @param string $email
	 * @return User
	 */
	public function getUserByEmail($email){
		$data = $this->users->findOne(["email" => $email]);

		return $this->setUser($data);
	}

	/**
	 * Sets up any database necessary
	 *
	 * @param int $code
	 * @return mixed
	 */
	public function makeDB($code){
		return;
	}

	public function verifyDB(){
		return 0;
	}

	/**
	 * Returns an array of video IDs that can be safely deleted
	 *
	 * @return array
	 */
	public function getPrunableVideos(){
		// Get maximum order ID for each user
		$cursor = $this->feeds->aggregate([['$group' =>
			['_id' => '$userID',
				'maxOrderID' => ['$max' => '$orderID']]]]);
		$usersAndMaxOrderIDs = [];
		foreach($cursor as $a){
			$usersAndMaxOrderIDs[$a["_id"]->__toString()] = $a["maxOrderID"];
		}

		// Get feed length for each user and set the order ID for the latest video that is out of their feed
		$usersAndMaxOutOfFeed = [];
		foreach($usersAndMaxOrderIDs as $userID => $maxOrderID){
			$result = $this->users->findOne(["_id" => $userID],
				["projection" => ["feedLength" => 1]]);
			$usersAndMaxOutOfFeed[$userID] = $maxOrderID - $result["feedLength"];
		}

		$videosInFeeds = [];
		$videosOutOfFeeds = [];
		foreach($usersAndMaxOutOfFeed as $userID => $maxOrderID){
			$userVideos = $this->feeds->find(["userID" => $userID],
				["projection" => ["videoID" => 1, "orderID" => 1, "userID" => 1]])->toArray();
			foreach($userVideos as $video){
				if($video["orderID"] < $maxOrderID){
					if(!in_array($video["videoID"], $videosOutOfFeeds, true)){
						$videosOutOfFeeds[] = $video["videoID"];
					}
				}
				else{
					if(!in_array($video["videoID"], $videosInFeeds, true)){
						$videosInFeeds[] = $video["videoID"];
					}
				}
			}
		}

		// Remove all videos that are in people's feeds from the list of videos out of people's feeds
		return array_diff($videosOutOfFeeds, $videosInFeeds);
	}
}
