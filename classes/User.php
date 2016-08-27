<?php
require_once __DIR__."/../header.php";

/**
 * Class User stores user specific information
 */
class User{
	private $username;
	private $email;
	private $fname;
	private $lname;
	private $gender;
	private $webID;
	private $passwd;
	private $userID;
	private $feedText;
	private $feedLength;

	/**
	 * User constructor.
	 */
	public function __construct(){
	}

	/**
	 * Checks if plaintext password when hashed, matches the hashed password stored in this User
	 *
	 * @param $passwd The password to check against the password stored in the database
	 * @return bool
	 */
	public function passwdCorrect($passwd){
		return hash("SHA512", $passwd.$this->username) == $this->getPasswd();
	}

	/**
	 * @return mixed
	 */
	public function getUserID(){
		return $this->userID;
	}

	/**
	 * @param mixed $userID
	 */
	public function setUserID($userID){
		$this->userID = $userID;
	}

	/**
	 * @return mixed
	 */
	public function getUsername(){
		return strtolower($this->username);
	}

	/**
	 * @param mixed $username
	 */
	public function setUsername($username){
		$username = strtolower($username);
		$this->username = $username;
	}

	/**
	 * @return mixed
	 */
	public function getEmail(){
		return strtolower($this->email);
	}

	/**
	 * @param mixed $email
	 */
	public function setEmail($email){
		$email = strtolower($email);
		$this->email = $email;
	}

	/**
	 * @return mixed
	 */
	public function getFname(){
		return $this->fname;
	}

	/**
	 * @param mixed $fname
	 */
	public function setFname($fname){
		$this->fname = $fname;
	}

	/**
	 * @return mixed
	 */
	public function getLname(){
		return $this->lname;
	}

	/**
	 * @param mixed $lname
	 */
	public function setLname($lname){
		$this->lname = $lname;
	}

	/**
	 * @return mixed
	 */
	public function getGender(){
		if($this->gender == ""){
			return 1;
		}
		return $this->gender;
	}

	/**
	 * @param mixed $gender
	 */
	public function setGender($gender){
		$this->gender = $gender;
	}

	/**
	 * @return mixed
	 */
	public function getWebID(){
		return $this->webID;
	}

	/**
	 * @param mixed $webID
	 */
	public function setWebID($webID){
		$this->webID = $webID;
	}

	/**
	 * @return mixed
	 */
	public function getPasswd(){
		return $this->passwd;
	}

	/**
	 * Sets hashed password using plaintext password and username
	 *
	 * @param mixed $passwd
	 * @throws \Exception Username must be set before setting the password because the password is stored as a hash of the plaintext password and the username
	 */
	public function setPasswd($passwd){
		if($this->username != ""){
			$passwd = hash("SHA512", $passwd.$this->username);
			$this->passwd = $passwd;
		}else{
			throw new Exception("Username needs to be set before setting password!");
		}
	}

	/**
	 * Used to set the hashed password from the database.
	 *
	 * @param $passwd Hashed password from database
	 */
	public function setPasswdDB($passwd){
		$this->passwd = $passwd;
	}

	/**
	 * @return mixed
	 */
	public function getFeedText(){
		return $this->feedText;
	}

	/**
	 * @param mixed $feedText
	 */
	public function setFeedText($feedText){
		$this->feedText = $feedText;
	}

	/**
	 * @return mixed
	 */
	public function getFeedLength(){
		return $this->feedLength;
	}

	/**
	 * @param mixed $feedLength
	 */
	public function setFeedLength($feedLength){
		$this->feedLength = $feedLength;
	}
}
