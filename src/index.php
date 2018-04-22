<?php
use \AudioDidact\GlobalFunctions;
require_once __DIR__ . "/header.php";

/**
 * Read request url path and split it into subdirectories. If one of the subdirectories is "User" and there is a
 * another subdirectory under that, then the user must be requesting a user page. If there is another subdirectory
 * called "feed", then the user is request the feed of a specific user.
 */
$path = parse_url($_SERVER['REQUEST_URI'])["path"];
$path = str_replace(strtolower(SUBDIR), "", strtolower($path));
$url = explode("/", $path);
$webID = "";
// Remove empty elements from the URL array
$url = array_filter($url);

// Make the homepage if requested
if(count($url) == 0 || (count($url) == 1 && $url[1] == "index.php")){
	// Verify user is logged in and their email has been verified
	if(GlobalFunctions::userIsLoggedIn() && ($_SESSION["user"]->isEmailVerified() || !EMAIL_ENABLED)){
		if(isset($_GET["manual"])){
			echo GlobalFunctions::generatePug("views/addVideoUpload.pug", "Add Content Manually");
		}
		else{
			$pageJS = 'public/js/addVideoURL.js';
			echo GlobalFunctions::generatePug("views/addVideo.pug",
				"Add Content",
				["addUserJSCheck" => GlobalFunctions::SRIChecksum(file_get_contents($pageJS))]);
		}
	}
	else{
		echo GlobalFunctions::generatePug("views/homepage.pug", "Home");
	}
	exit(0);
}
else if(count($url) == 1){
	$u = $url[1];
	if($u == "faq"){
		echo GlobalFunctions::generatePug("views/faq.pug", "FAQ");
		exit(0);
	}
	else if($u == "help"){
		echo GlobalFunctions::generatePug("views/help.pug", "Help");
		exit(0);
	}
	else if($u == "forgot" && EMAIL_ENABLED){
		if(isset($_GET["recoveryCode"]) && isset($_GET["username"])){
			makePasswordReset($_GET["username"], $_GET["recoveryCode"]);
			exit(0);
		}
		else if(!GlobalFunctions::userIsLoggedIn()){
			echo GlobalFunctions::generatePug("views/passwordResetRequest.pug", "Request a Password Reset");
			exit(0);
		}
	}
	else if($u == "resetpassword" && EMAIL_ENABLED){
		if(GlobalFunctions::fullVerifyCSRF() && isset($_GET["uname"]) && isset($_GET["code"]) && isset($_GET["passwd"])){
			resetPassword($_GET["uname"], $_GET["passwd"], $_GET["code"]);
			exit(0);
		}
		else if(GlobalFunctions::fullVerifyCSRF() && isset($_GET["uname"]) && EMAIL_ENABLED){
			makePasswordResetRequest($_GET["uname"]);
			exit(0);
		}
	}
	else if($u == "signup" || $u == "signup.php"){
		// Check if a user is signing up or needs the sign up webpage
		if($_SERVER['REQUEST_METHOD'] == "POST" && GlobalFunctions::fullVerifyCSRF()){
			// Check that required variables are present and are not empty.
			if(isset($_POST["uname"]) && isset($_POST["passwd"]) && isset($_POST["email"])){
				$dal = GlobalFunctions::getDAL();
				$u = new AudioDidact\User();
				$statement = $u->signup($_POST["uname"], $_POST["passwd"], $_POST["email"], $dal);
				echo $statement;
				if(!mb_strpos($statement, "failed")){
					GlobalFunctions::userLogIn($dal->getUserByUsername($u->getUsername()));
				}
			}
			else{
				echo "Sign Up Failed!\nNo email, username, or password specified!";
			}
		}
		else{
			echo GlobalFunctions::generatePug('views/signup.pug', 'Sign Up for AudioDidact');
		}
		exit(0);
	}
	else if($u == "login" || $u == "login.php" || $u == "logout"){
		// Check if the user is requesting a logout
		if(GlobalFunctions::fullVerifyCSRF()
			&& ((isset($_POST["action"]) && $_POST["action"] == "logout") || $u == "logout")){
			GlobalFunctions::userLogOut();
			echo "Logout Success!";
			exit(0);
		}

		// Make sure necessary variables are given
		if(GlobalFunctions::fullVerifyCSRF() && isset($_POST["uname"]) && isset($_POST["passwd"])){
			// Check login info, set loggedIn to true if the information is correct
			$dal = GlobalFunctions::getDAL();
			$possibleUser = $dal->getUserByUsername($_POST["uname"]);
			$possibleUserEmail = $dal->getUserByEmail($_POST["uname"]);
			// Check user based on username
			if($possibleUser != null && $possibleUser->passwdCorrect($_POST["passwd"])){
				GlobalFunctions::userLogIn($possibleUser);
				echo "Login Success!";
			}
			// Check user based on email
			else if($possibleUserEmail != null && $possibleUserEmail->passwdCorrect($_POST["passwd"])){
				GlobalFunctions::userLogIn($possibleUserEmail);
				echo "Login Success!";
			}
			else{
				GlobalFunctions::userLogOut();
				echo "Login Failed!";
			}
		}
		else{
			GlobalFunctions::userLogOut();
			echo "Login Failed!";
		}
		exit(0);
	}
}
// Handle user pages
else{
	foreach($url as $k => $u){
		if($u == "user" && isset($url[$k + 1])){
			$webID = $url[$k + 1];
			if(isset($url[$k + 2]) && $url[$k + 2] == "feed"){
				printUserFeed($webID);
				exit(0);
			}
			else if(!isset($url[$k + 2]) || $url[$k + 2] == ""){
				$edit = false;
				if(GlobalFunctions::userIsLoggedIn() && $_SESSION["user"]->getWebID() == $webID){
					$edit = true;
				}
				require_once "userPageGenerator.php";
				if(isset($_GET["verifyEmail"])){
					echo makeUserPage($webID, $edit, $_GET["verifyEmail"]);
				}
				else{
					echo makeUserPage($webID, $edit);
				}
				exit(0);
			}
		}
	}
}
make404();

/**
 * Generates password reset codes and emails link to a verified email address
 *
 * @param $username string Username or Email address of user
 */
function makePasswordResetRequest($username){
	$dal = GlobalFunctions::getDAL();
	$possibleUser = $dal->getUserByUsername($username);
	$possibleUserEmail = $dal->getUserByEmail($username);
	// Check user based on username
	if($possibleUser != null && $possibleUser->isEmailVerified()){
		$possibleUser->setPasswordRecoveryCodes([]);
		$possibleUser->addPasswordRecoveryCode();
		$dal->updateUserEmailPasswordCodes($possibleUser);
		\AudioDidact\EMail::sendForgotPasswordEmail($possibleUser);
		echo "Password Reset Email Sent!";
	}
	// Check user based on email
	else if($possibleUserEmail != null && $possibleUserEmail->isEmailVerified()){
		$possibleUserEmail->setPasswordRecoveryCodes([]);
		$possibleUserEmail->addPasswordRecoveryCode();
		$dal->updateUserEmailPasswordCodes($possibleUserEmail);
		\AudioDidact\EMail::sendForgotPasswordEmail($possibleUserEmail);
		echo "Password Reset Email Sent!";
	}
	else{
		echo "Password reset failed. Username or email address not found, or user's email was not verified.";
	}
}

/**
 * Verifies given code and changes password for the user
 *
 * @param $username string username of user to change password of
 * @param $password string password to change to
 * @param $code string password reset code
 */
function resetPassword($username, $password, $code){
	$dal = GlobalFunctions::getDAL();
	$user = $dal->getUserByUsername($username);
	if($user != null){
		if($user->verifyPasswordRecoveryCode($code)){
			// Change user details
			$user->setPasswd($password);
			$user->setPasswordRecoveryCodes([]);
			// Save changes to DB
			$dal->updateUserPassword($user);
			$dal->updateUserEmailPasswordCodes($user);
			// Log the user in with the new credentials
			GlobalFunctions::userLogIn($user);
			\AudioDidact\EMail::sendPasswordWasResetEmail($user);
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

/**
 * Verifies request code and then generates page get the user's new password and reset it
 *
 * @param $username
 * @param $code
 */
function makePasswordReset($username, $code){
	$dal = GlobalFunctions::getDAL();
	$requestedUser = $dal->getUserByUsername($username);
	if($requestedUser->verifyPasswordRecoveryCode($code)){
		$options = ["passwordresetcode" => $code, "user" => $requestedUser];
		echo GlobalFunctions::generatePug("views/passwordResetPage.pug", "Reset Your Password", $options);
	}
	else{
		echo '<script type="text/javascript">location.assign("/' . SUBDIR . '");</script>';
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
 *
 * @param $webID string WebID of the requested feed
 */
function printUserFeed($webID){
	$dal = GlobalFunctions::getDAL();
	$requestedUser = $dal->getUserByWebID($webID);
	if($requestedUser == null){
		error_log("user " . $webID . " not found/is null");
		http_response_code(404);

		return;
	}
	if($requestedUser->isPrivateFeed() && httpAuthenticate($dal)){
		header('Content-Type: application/xml; charset=utf-8');
		echo $requestedUser->getFeedText();
	}
	else if(!$requestedUser->isPrivateFeed()){
		header('Content-Type: application/xml; charset=utf-8');
		echo $requestedUser->getFeedText();
	}
}

/**
 * Sends HTTP Basic Authentication headers to the user and authenticates against the database
 *
 * @param \AudioDidact\DB\DAL $dal
 * @return bool
 */
function httpAuthenticate(\AudioDidact\DB\DAL $dal){
	if(!isset($_SERVER['PHP_AUTH_USER'])){
		header('WWW-Authenticate: Basic realm="Private User Feed"');
		header('HTTP/1.0 401 Unauthorized');
		echo "User must be authenticated to continue";

		return false;
	}
	else if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) &&
		$dal->usernameExists($_SERVER['PHP_AUTH_USER']) &&
		$dal->getUserByUsername($_SERVER['PHP_AUTH_USER'])->passwdCorrect($_SERVER['PHP_AUTH_PW'])
	){
		return true;
	}
	else{
		header('WWW-Authenticate: Basic realm="Private User Feed"');
		header('HTTP/1.0 401 Unauthorized');
		echo "Incorrect username or password.\nUser must be authenticated to continue";

		return false;
	}
}
