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
 * @param $webID the WebID of the requeste feed
 */
function returnUserFeed($webID){
	header('Content-Type: application/rss+xml; charset=utf-8');
	$myDalClass = ChosenDAL;
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
	echo $dal->getFeedText($dal->getUserByWebID($webID));
}
