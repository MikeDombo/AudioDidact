<?php
require_once __DIR__."/header.php";

/**
 * Read request url path and split it into subdirectories. If one of the subdirectories is "User" and there is a
 * another subdirectory under that, then the user must be requesting a user page. If there is another subdirectory
 * called "feed", then the user is request the feed of a specific user.
 */
$url = explode("/", parse_url($_SERVER['REQUEST_URI'])["path"]);
$webID = "";
foreach($url as $k=>$u){
	if($u == "user" && isset($url[$k+1])){
		$webID = $url[$k+1];
		if(isset($url[$k+2]) && $url[$k+2] == "feed"){
			returnUserFeed($webID);
			exit(0);
		}
		else if(!isset($url[$k+2]) || $url[$k+2] == ""){
			printUserPage($webID);
			exit(0);
		}
	}
}
make404();

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
 * @param $webID The WebID of the requested feed
 */
function returnUserFeed($webID){
	$myDalClass = ChosenDAL;
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);

	$requestedUser = $dal->getUserByWebID($webID);
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

/**
 *  Make the user profile page
 * @param string User's webID
 */
 function printUserPage($webID){
	require_once __DIR__."/viewUser.php";
	userPage($webID);
 }
 