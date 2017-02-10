<?php
require_once __DIR__."/../header.php";

/**
 * Class User stores user specific information
 */
class User{
	/** @var  string $username is the username */
	private $username;
	/** @var  string $email is the email */
	private $email;
	/** @var  string $fname is the first name */
	private $fname;
	/** @var  string $lname is the last name */
	private $lname;
	/** @var  int $gender is the gender as an integer */
	private $gender;
	/** @var  string $webID is the webID */
	private $webID;
	/** @var  string $passwd is the hashed password */
	private $passwd;
	/** @var  int $userID is the unique identifier assigned by the database */
	private $userID;
	/** @var  string $feedText is the full xml text of the feed */
	private $feedText;
	/** @var  int $feedLength is the maximum number of items in the feed */
	private $feedLength;
	/** @var  array $feedDetails is an associative array containing the details used to make the feed */
	private $feedDetails;
	/** @var  bool $privateFeed true if the user's feed should be protected by HTTP Basic Authentication */
	private $privateFeed;

	/**
	 * User constructor.
	 */
	public function __construct(){
		$this->feedDetails = ["title"=>"AudioDidact",
			"description"=>"Learn by putting audio and video from multiple sources into a portable podcast feed.",
			"icon"=>LOCAL_URL."public/img/favicon/favicon-512x512.png",
			"itunesAuthor"=>"Michael Dombrowski"];
	}

	/**
	 * Validates names and other strings using PHP FILTER_VALIDATE_EMAIL. Returns true if the string is valid
	 * @param $email
	 * @return bool
	 */
	public function validateEmail($email){
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Validates names and other strings using PHP FILTER_SANITIZE_STRING. Returns true if the string is valid
	 * @param $name
	 * @return bool
	 */
	public function validateName($name){
		return filter_var($name, FILTER_SANITIZE_STRING) == $name;
	}

	/**
	 * Validates webID so it can only contain alphanumerics _,-,@, and $. Returns true if the string is valid
	 * @param $webID
	 * @return bool
	 */
	public function validateWebID($webID){
		return $webID == preg_replace("/[^a-zA-Z0-9_\-~@\$]/", "", $webID);
	}

	/**
	 * Checks if plaintext password when hashed, matches the hashed password stored in this User
	 *
	 * @param string $passwd The password to check against the password stored in the database
	 * @return bool
	 */
	public function passwdCorrect($passwd){
		if(strpos($this->getPasswd(), '$2y$12$') !== false){
			return password_verify($passwd, $this->getPasswd());
		}
		else{
			// Check password using old scheme
			$result = hash("SHA512", $passwd.$this->username) == $this->getPasswd();
			// If the password was correct, then update it to the new bcrypt scheme
			if($result){
				$this->setPasswd($passwd);
				require_once __DIR__."/../config.php";
				$myDalClass = ChosenDAL;
				$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
				/** @var $dal \DAL */
				$dal->updateUserPassword($this);
			}
			return $result;
		}
	}

	/**
	 * Gets user ID
	 * @return mixed
	 */
	public function getUserID(){
		return $this->userID;
	}

	/**
	 * Sets user ID
	 * @param mixed $userID
	 */
	public function setUserID($userID){
		$this->userID = $userID;
	}

	/**
	 * Gets username in lowercase
	 * @return mixed
	 */
	public function getUsername(){
		return strtolower($this->username);
	}

	/**
	 * Sets username in lowercase
	 * @param mixed $username
	 */
	public function setUsername($username){
		$username = strtolower($username);
		$this->username = $username;
	}

	/**
	 * Gets email in lowercase
	 * @return mixed
	 */
	public function getEmail(){
		return strtolower($this->email);
	}

	/**
	 * Sets email in lower case
	 * @param mixed $email
	 */
	public function setEmail($email){
		$email = strtolower($email);
		$this->email = $email;
	}

	/**
	 * Gets first name
	 * @return mixed
	 */
	public function getFname(){
		return $this->fname;
	}

	/**
	 * Sets first name
	 * @param mixed $fname
	 */
	public function setFname($fname){
		$this->fname = $fname;
	}

	/**
	 * Gets last name
	 * @return mixed
	 */
	public function getLname(){
		return $this->lname;
	}

	/**
	 * Sets last name
	 * @param mixed $lname
	 */
	public function setLname($lname){
		$this->lname = $lname;
	}

	/**
	 * Gets gender as integer, or if not set, returns 1 (Male)
	 * @return int
	 */
	public function getGender(){
		if($this->gender == ""){
			return 1;
		}
		return $this->gender;
	}

	/**
	 * Sets gender
	 * @param int $gender
	 */
	public function setGender($gender){
		$this->gender = $gender;
	}

	/**
	 * Gets webID
	 * @return mixed
	 */
	public function getWebID(){
		return $this->webID;
	}

	/**
	 * Sets webID
	 * @param string $webID
	 */
	public function setWebID($webID){
		$this->webID = $webID;
	}

	/**
	 * Gets hashed password
	 * @return mixed
	 */
	public function getPasswd(){
		return $this->passwd;
	}

	/**
	 * Sets hashed password using plaintext password and username
	 *
	 * @param string $passwd
	 * @throws \Exception Username must be set before setting the password because the password is stored as a hash of the plaintext password and the username
	 */
	public function setPasswd($passwd){
		$options = ['cost' => 12];
		$this->passwd = password_hash($passwd, PASSWORD_BCRYPT, $options);
	}

	/**
	 * Used to set the hashed password from the database.
	 *
	 * @param string $passwd Hashed password from database
	 */
	public function setPasswdDB($passwd){
		$this->passwd = $passwd;
	}

	/**
	 * Gets feed text
	 * @return string
	 */
	public function getFeedText(){
		return $this->feedText;
	}

	/**
	 * Sets feed text
	 * @param string $feedText
	 */
	public function setFeedText($feedText){
		$this->feedText = $feedText;
	}

	/**
	 * Gets feed length
	 * @return int
	 */
	public function getFeedLength(){
		return $this->feedLength;
	}

	/**
	 * Sets feed length
	 * @param int $feedLength
	 */
	public function setFeedLength($feedLength){
		$this->feedLength = $feedLength;
	}

	/**
	 * Gets the feed detail array
	 * @return array
	 */
	public function getFeedDetails(){
		return $this->feedDetails;
	}

	/**
	 * Sets the feed detail array
	 * @param array $feedDetails
	 */
	public function setFeedDetails($feedDetails){
		$this->feedDetails = $feedDetails;
	}

	/**
	 * @return boolean
	 */
	public function isPrivateFeed(){
		return $this->privateFeed;
	}

	/**
	 * @param boolean $privateFeed
	 */
	public function setPrivateFeed($privateFeed){
		$this->privateFeed = $privateFeed;
	}

}
