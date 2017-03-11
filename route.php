<?php
require_once __DIR__."/header.php";

/**
 * Read request url path and split it into subdirectories. If one of the subdirectories is "User" and there is a
 * another subdirectory under that, then the user must be requesting a user page. If there is another subdirectory
 * called "feed", then the user is request the feed of a specific user.
 */
$path = parse_url($_SERVER['REQUEST_URI'])["path"];
$path = str_replace(strtolower(SUBDIR), "", strtolower($path));
$url = explode("/", $path);
$webID = "";
foreach($url as $k=>$u){
	if($u == "user" && isset($url[$k+1])){
		$webID = $url[$k+1];
		if(isset($url[$k+2]) && $url[$k+2] == "feed"){
			printUserFeed($webID);
			exit(0);
		}
		else if(!isset($url[$k+2]) || $url[$k+2] == ""){
			$loggedin = "false";
			if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
				$loggedin = "true";
			}
			if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && $_SESSION["user"]->getWebID() == $webID){
				$title = "User Page | $webID | Edit";
				$edit = true;
			}else{
				$title = "User Page | $webID";
				$edit = false;
			}
			require_once "userPageGenerator.php";
			if(isset($_GET["verifyEmail"]) && $edit){
				echo makeUserPage($webID, $edit, $loggedin, $_GET["verifyEmail"]);
			}
			else{
				echo makeUserPage($webID, $edit, $loggedin);
			}
			exit(0);
		}
	}
	else if($u == "faq"){
		echo generatePug("views/faq.pug", "FAQ");
		exit(0);
	}
	else if($u == "help"){
		echo generatePug("views/help.pug", "Help");
		exit(0);
	}
	else if($u == "forgot"){
		if(isset($_GET["recoveryCode"]) && isset($_GET["username"])){
			makePasswordReset($_GET["username"], $_GET["recoveryCode"]);
			exit(0);
		}
		else if(!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"] || $_SESSION["user"] == null){
			echo generatePug("views/passwordResetRequest.pug", "Request a Password Reset");
			exit(0);
		}
	}
	else if($u == "resetpassword"){
		if(isset($_GET["uname"]) && isset($_GET["code"]) && isset($_GET["passwd"])){
			resetPassword($_GET["uname"], $_GET["passwd"], $_GET["code"]);
			exit(0);
		}
		else if(isset($_GET["uname"])){
			makePasswordResetRequest($_GET["uname"]);
			exit(0);
		}
	}
}
make404();

function makePasswordResetRequest($username){
	$myDalClass = ChosenDAL;
	/** @var \DAL $dal */
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
	$possibleUser = $dal->getUserByUsername($username);
	$possibleUserEmail = $dal->getUserByEmail($username);
	// Check user based on username
	if($possibleUser != null && $possibleUser->isEmailVerified()){
		$possibleUser->setPasswordRecoveryCodes([]);
		$possibleUser->addPasswordRecoveryCode();
		$dal->updateUserEmailPasswordCodes($possibleUser);
		EMail::sendForgotPasswordEmail($possibleUser);
		echo "Password Reset Email Sent!";
	}
	// Check user based on email
	else if($possibleUserEmail != null && $possibleUserEmail->isEmailVerified()){
		$possibleUserEmail->setPasswordRecoveryCodes([]);
		$possibleUserEmail->addPasswordRecoveryCode();
		$dal->updateUserEmailPasswordCodes($possibleUserEmail);
		EMail::sendForgotPasswordEmail($possibleUserEmail);
		echo "Password Reset Email Sent!";
	}
	else{
		echo "Password reset failed. Username or email address not found, or user's email was not verified.";
	}
}

function resetPassword($username, $password, $code){
	$myDalClass = ChosenDAL;
	/** @var \DAL $dal */
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
	$user = $dal->getUserByUsername($username);
	if($user != null){
		if($user->verifyPasswordRecoveryCode($code)){
			$user->setPasswd($password);
			$user->setPasswordRecoveryCodes([]);
			$dal->updateUserPassword($user);
			$dal->updateUserEmailPasswordCodes($user);
			// Log the user in with the new credentials
			$_SESSION["user"] = $user;
			$_SESSION["loggedIn"] = true;
			EMail::sendPasswordWasResetEmail($user);
			echo "Success!";
		}
		else{
			echo "Failed: bad code given";
		}
	}
	else{
		echo "Failed: username not found.";
	}
}

function makePasswordReset($username, $code){
	$myDalClass = ChosenDAL;
	/** @var \DAL $dal */
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
	$requestedUser = $dal->getUserByUsername($username);
	if($requestedUser->verifyPasswordRecoveryCode($code)){
		$options = ["passwordresetcode"=> $code, "user"=>$requestedUser];
		echo generatePug("views/passwordResetPage.pug", "Reset Your Password", $options);
	}
	else{
		echo '<script>location.assign("/'.SUBDIR.'");</script>';
	}
}

/**
 * Send a 404 error and show "Page not found"
 */
function make404(){
	header("HTTP/1.0 404 Not Found");
	echo "404: Page Not Found!";
	exit(0);
}

/**
 * Make the user feed by reading it from the database.
 * @param $webID string WebID of the requested feed
 */
function printUserFeed($webID){
	$myDalClass = ChosenDAL;
	/** @var DAL $dal */
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);

	$requestedUser = $dal->getUserByWebID($webID);
	if($requestedUser == null){
		error_log("user ".$webID." not found/is null");
		http_response_code(404);
		return;
	}
	if($requestedUser->isPrivateFeed() && httpAuthenticate($dal)){
		header('Content-Type: application/xml; charset=utf-8');
		echo $dal->getFeedText($requestedUser);
	}
	else if(!$requestedUser->isPrivateFeed()){
		header('Content-Type: application/xml; charset=utf-8');
		echo $dal->getFeedText($requestedUser);
	}
}

/**
 * Sends HTTP Basic Authentication headers to the user and authenticates against the database
 * @param DAL $dal
 * @return bool
 */
function httpAuthenticate(DAL $dal){
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		header('WWW-Authenticate: Basic realm="Private User Feed"');
		header('HTTP/1.0 401 Unauthorized');
		echo "User must be authenticated to continue";
		return false;
	}
	else if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) &&
			$dal->usernameExists($_SERVER['PHP_AUTH_USER']) &&
			$dal->getUserByUsername($_SERVER['PHP_AUTH_USER'])->passwdCorrect($_SERVER['PHP_AUTH_PW'])) {
		return true;
	}
	else{
		header('WWW-Authenticate: Basic realm="Private User Feed"');
		header('HTTP/1.0 401 Unauthorized');
		echo "User must be authenticated to continue";
		return false;
	}
}
