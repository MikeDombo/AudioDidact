<?php
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");
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

	public function __construct(){
	}

	public function passwdCorrect($passwd){
		if(hash("SHA512", $passwd.$this->username) == $this->getPasswd()){
			return true;
		}
		else{
			return false;
		}
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
	 * @param mixed $passwd
	 * @throws \Exception
	 */
	public function setPasswd($passwd){
		if($this->username != ""){
			$passwd = hash("SHA512", $passwd.$this->username);
			$this->passwd = $passwd;
		}
		else{
			throw new Exception("Username needs to be set before setting password!");
		}
	}

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

}
?>