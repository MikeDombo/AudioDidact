<?php

namespace AudioDidact;

use AudioDidact\DB\DAL;

require_once __DIR__ . "/../header.php";

/**
 * Class User stores user specific information
 */
class User {
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
	/** @var  bool $emailVerified true when the user has verified their email address */
	private $emailVerified;
	/** @var  array $emailVerificationCodes a dictionary of valid email verification response codes
	 * values are in the form of [["code"=xyz, "expiration"=xyz]]*/
	private $emailVerificationCodes;
	/** @var  array $passwordRecoveryCodes a dictionary of valid password recovery response codes
	 * values are in the form of [["code"=xyz, "expiration"=xyz]]*/
	private $passwordRecoveryCodes;

	/**
	 * User constructor.
	 */
	public function __construct(){
		$this->feedDetails = ["title" => "AudioDidact",
			"description" => "Learn by putting audio and video from multiple sources into a portable podcast feed.",
			"icon" => LOCAL_URL . "public/img/favicon/favicon-512x512.png",
			"itunesAuthor" => "Michael Dombrowski"];
		$this->emailVerificationCodes = [];
		$this->passwordRecoveryCodes = [];
		$this->emailVerified = false;
	}

	public function signup($uname, $passwd, $email, DAL $dal, $sendEmail = true){
		if(trim($uname) == "" || trim($passwd) == "" || trim($email) == ""){
			return "Sign up failed:\nUsername, password, or email is empty!";
		}
		// Disallow spaces in usernames, but automatically correct it
		$uname = trim($uname);

		if(!$this->validatePassword($passwd)){
			return "Sign up failed:\nPassword must be greater than 6 characters long!";
		}

		// Make sure the username and email address are not taken.
		if($dal->emailExists($email) || $dal->usernameExists($uname)){
			return "Sign up failed:\nUsername or email already in use!";
		}

		if(!$this->validateEmail($email)){
			return "Sign up failed:\nInvalid Email Address!";
		}
		if(!$this->validateWebID($uname)){
			return "Sign up failed:\nUsername contains invalid characters!";
		}

		$this->setUsername($uname);
		$this->setEmail($email);
		$this->setPasswd($passwd);
		$this->setWebID($uname);
		$this->setPrivateFeed(false);
		$this->setFeedLength(25);
		$this->setFeedText(PodTube::makeFullFeed($this, $dal, true)->generateFeed());
		$this->setEmailVerified(false);

		// Add user to db and send email to verify
		try{
			$dal->addUser($this);
			$user = $dal->getUserByUsername($uname);
			$user->addEmailVerificationCode();
			$dal->updateUserEmailPasswordCodes($user);
			if($sendEmail && EMAIL_ENABLED){
				EMail::sendVerificationEmail($user);
			}
		}
		catch(\Exception $e){
			error_log($e);

			return "Sign up failed due to an unknown error.\nPlease contact the developer from the help page.";
		}

		return "Sign up success!";
	}

	/**
	 * Validates password for length
	 *
	 * @param $password
	 * @return bool
	 */
	public function validatePassword($password){
		return mb_strlen($password) >= 6;
	}

	/**
	 * Validates names and other strings using PHP FILTER_VALIDATE_EMAIL. Returns true if the string is valid
	 *
	 * @param $email
	 * @return bool
	 */
	public function validateEmail($email){
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Validates webID so it can only contain alphanumerics _,-,@, and $. Returns true if the string is valid
	 *
	 * @param $webID
	 * @return bool
	 */
	public function validateWebID($webID){
		return $webID == mb_ereg_replace("[^a-zA-Z0-9_\-~@\$]", "", $webID) && mb_strlen($webID) > 0;
	}

	/**
	 * Generates a random code and adds it to the list of email verification codes
	 *
	 * @return array
	 */
	public function addEmailVerificationCode(){
		// Make a new code and set the expiration for 24 hours
		$newRandomCode = $this->generateRandomCode();
		$this->emailVerificationCodes[] = $newRandomCode;

		return $newRandomCode;
	}

	/**
	 * returns a dictionary in the form of ["code"=random, "expiration"=24hours from now]
	 *
	 * @return array
	 */
	private function generateRandomCode(){
		return ["code" => md5(uniqid(rand(), true)), "expiration" => time() + (60 * 60 * 24)];
	}

	/**
	 * verifies that a given email verification code is valid for this user
	 *
	 * @param $c
	 * @return bool
	 */
	public function verifyEmailVerificationCode($c){
		return $this->verifyCodes($this->emailVerificationCodes, $c);
	}

	/**
	 * Generic function to check that a given code is in a given code set and the expiration has not been exceeded
	 *
	 * @param $existingCodes
	 * @param $toCheck
	 * @return bool
	 */
	private function verifyCodes($existingCodes, $toCheck){
		foreach($existingCodes as $codes){
			if($codes["code"] == $toCheck && $codes["expiration"] >= time()){
				return true;
			}
		}

		return false;
	}

	/**
	 * Generates a random code and adds it to the list of password recovery codes
	 *
	 * @return array
	 */
	public function addPasswordRecoveryCode(){
		// Make a new code and set the expiration for 24 hours
		$newRandomCode = $this->generateRandomCode();
		$this->passwordRecoveryCodes[] = $newRandomCode;

		return $newRandomCode;
	}

	/**
	 * verifies that a given password recovery code is valid for this user
	 *
	 * @param $c
	 * @return bool
	 */
	public function verifyPasswordRecoveryCode($c){
		return $this->verifyCodes($this->passwordRecoveryCodes, $c);
	}

	/**
	 * @return bool
	 */
	public function isEmailVerified(){
		return $this->emailVerified;
	}

	/**
	 * @param bool $emailVerified
	 */
	public function setEmailVerified($emailVerified){
		$this->emailVerified = boolval($emailVerified);
	}

	/**
	 * @return array
	 */
	public function getEmailVerificationCodes(){
		return $this->emailVerificationCodes;
	}

	/**
	 * @param array $emailVerificationCodes
	 */
	public function setEmailVerificationCodes($emailVerificationCodes){
		$this->emailVerificationCodes = $emailVerificationCodes;
	}

	/**
	 * @return array
	 */
	public function getPasswordRecoveryCodes(){
		return $this->passwordRecoveryCodes;
	}

	/**
	 * @param array $passwordRecoveryCodes
	 */
	public function setPasswordRecoveryCodes($passwordRecoveryCodes){
		$this->passwordRecoveryCodes = $passwordRecoveryCodes;
	}

	/**
	 * Validates names and other strings using PHP FILTER_SANITIZE_STRING. Returns true if the string is valid
	 *
	 * @param $name
	 * @return bool
	 */
	public function validateName($name){
		return filter_var($name, FILTER_SANITIZE_STRING) == $name;
	}

	/**
	 * Checks if plaintext password when hashed, matches the hashed password stored in this User
	 *
	 * @param string $passwd The password to check against the password stored in the database
	 * @return bool
	 */
	public function passwdCorrect($passwd){
		if(mb_strpos($this->getPasswd(), '$2y$12$') !== false){
			return password_verify($passwd, $this->getPasswd());
		}
		else{
			// Check password using old scheme
			$result = hash("SHA512", $passwd . $this->username) == $this->getPasswd();
			// If the password was correct, then update it to the new bcrypt scheme
			if($result){
				$this->setPasswd($passwd);
				require_once __DIR__ . "/../config.php";
				$dal = GlobalFunctions::getDAL();
				$dal->updateUserPassword($this);
			}

			return $result;
		}
	}

	/**
	 * Gets hashed password
	 *
	 * @return mixed
	 */
	public function getPasswd(){
		return $this->passwd;
	}

	/**
	 * Sets hashed password using plaintext password and username
	 *
	 * @param string $passwd
	 * @throws \Exception Username must be set before setting the password because the password is stored as a hash of
	 *     the plaintext password and the username
	 */
	public function setPasswd($passwd){
		$options = ['cost' => 12];
		$this->passwd = password_hash($passwd, PASSWORD_BCRYPT, $options);
	}

	/**
	 * Gets user ID
	 *
	 * @return mixed
	 */
	public function getUserID(){
		return $this->userID;
	}

	/**
	 * Sets user ID
	 *
	 * @param mixed $userID
	 */
	public function setUserID($userID){
		$this->userID = $userID;
	}

	/**
	 * Gets username in lowercase
	 *
	 * @return mixed
	 */
	public function getUsername(){
		return mb_strtolower($this->username);
	}

	/**
	 * Sets username in lowercase
	 *
	 * @param mixed $username
	 */
	public function setUsername($username){
		$username = mb_strtolower($username);
		$this->username = $username;
	}

	/**
	 * Gets email in lowercase
	 *
	 * @return mixed
	 */
	public function getEmail(){
		return mb_strtolower($this->email);
	}

	/**
	 * Sets email in lower case
	 *
	 * @param mixed $email
	 */
	public function setEmail($email){
		$email = mb_strtolower($email);
		$this->email = $email;
	}

	/**
	 * Gets first name
	 *
	 * @return mixed
	 */
	public function getFname(){
		return $this->fname;
	}

	/**
	 * Sets first name
	 *
	 * @param mixed $fname
	 */
	public function setFname($fname){
		$this->fname = $fname;
	}

	/**
	 * Gets last name
	 *
	 * @return mixed
	 */
	public function getLname(){
		return $this->lname;
	}

	/**
	 * Sets last name
	 *
	 * @param mixed $lname
	 */
	public function setLname($lname){
		$this->lname = $lname;
	}

	/**
	 * Gets gender as integer, or if not set, returns 1 (Male)
	 *
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
	 *
	 * @param int $gender
	 */
	public function setGender($gender){
		$this->gender = $gender;
	}

	/**
	 * Gets webID
	 *
	 * @return mixed
	 */
	public function getWebID(){
		return $this->webID;
	}

	/**
	 * Sets webID
	 *
	 * @param string $webID
	 */
	public function setWebID($webID){
		$this->webID = $webID;
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
	 *
	 * @return string
	 */
	public function getFeedText(){
		return $this->feedText;
	}

	/**
	 * Sets feed text
	 *
	 * @param string $feedText
	 */
	public function setFeedText($feedText){
		$this->feedText = $feedText;
	}

	/**
	 * Gets feed length
	 *
	 * @return int
	 */
	public function getFeedLength(){
		return $this->feedLength;
	}

	/**
	 * Sets feed length
	 *
	 * @param int $feedLength
	 */
	public function setFeedLength($feedLength){
		$this->feedLength = intval($feedLength);
	}

	/**
	 * Gets the feed detail array
	 *
	 * @return array
	 */
	public function getFeedDetails(){
		return $this->feedDetails;
	}

	/**
	 * Sets the feed detail array
	 *
	 * @param array $feedDetails
	 */
	public function setFeedDetails($feedDetails){
		$this->feedDetails = $feedDetails;
	}

	/**
	 * @return boolean
	 */
	public function isPrivateFeed(){
		if(empty($this->privateFeed)){
			return false;
		}
		return $this->privateFeed;
	}

	/**
	 * @param boolean $privateFeed
	 */
	public function setPrivateFeed($privateFeed){
		$this->privateFeed = boolval($privateFeed);
	}

}
