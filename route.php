<?php
spl_autoload_register(function($class){
	require_once __DIR__.'/classes/MySQLDAL.php';
	require_once __DIR__.'/classes/User.php';
});

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

function make404(){
	header("HTTP/1.0 404 Not Found");
	echo "404: Page Not Found!";
	exit(0);
}

function returnUserFeed($webID){
	header('Content-Type: application/rss+xml; charset=utf-8');
	$db = "podtube";
	$dbUser = "podtube";
	$dbPass = "podtube";
	$dal = new MySQLDAL("localhost", $db, $dbUser, $dbPass);
	echo $dal->getFeedText($dal->getUserByWebID($webID));
}
?>