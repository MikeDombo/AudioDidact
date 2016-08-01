<?php
spl_autoload_register(function($class){
	require_once __DIR__.'/YouTube.php';
	require_once __DIR__.'/PodTube.php';
	require_once __DIR__.'/classes/MySQLDAL.php';
	require_once __DIR__.'/classes/User.php';
	require_once __DIR__.'/classes/Video.php';
});

ignore_user_abort(true);
ini_set('max_execution_time', 0);
ob_implicit_flush(true);
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}


$myURL = "http://example.com/"; // Change to your hostname
$googleAPIServerKey = "*******"; // Add server key here
$db = "podtube";
$dbUser = "podtube";
$dbPass = "podtube";


$downloadPath = "temp";

$dal = new MySQLDAL("localhost", $db, $dbUser, $dbPass);
if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
	$user = $_SESSION["user"];
}
else{
	echo json_encode(['stage'=>-1, 'error'=>"Must be logged in to continue!", 'progress'=>0]);
	exit(1);
}

if(isset($_GET["yt"]) || (isset($argv) && isset($argv[2]))){
	if(isset($argv) && isset($argv[2])){
		$_GET["yt"] = $argv[2];
	}
	$podtube = new PodTube($dal, $myURL, $downloadPath);
	$download = new YouTube($_GET["yt"], $podtube, $googleAPIServerKey, $downloadPath);

	$video = new Video();
	$video->setDesc($download->getDescr());
	$video->setAuthor($download->getVideoAuthor());
	$video->setTitle($download->getVideoTitle());
	$video->setId($download->getVideoID());

	if(!$dal->inFeed($video, $user)){
		$dal->addVideo($video, $user);
	}

	// If not all thumbnail, video, and audio are downloaded, then download them in that order
	if(!$download->allDownloaded()){
		$download->downloadThumbnail();
		$download->downloadVideo();
		$download->convert();
	}

	// Before we make the feed, check that every file is downloaded
	$items = $dal->getFeed($user);
	for($x=0;$x<$user->getFeedLength() && isset($items[$x]);$x++){
		if(!file_exists($downloadPath.DIRECTORY_SEPARATOR.$items[$x]->getId().".mp3") || !file_exists($downloadPath
				.DIRECTORY_SEPARATOR.$items[$x]->getId().".jpg")){
			$download = new YouTube($items[$x]->getId(), $podtube, $googleAPIServerKey, $downloadPath);
			if(!$download->allDownloaded()){
				$download->downloadThumbnail();
				$download->downloadVideo();
				$download->convert();
			}
		}
	}

	// Create the RSS feed from the existing CSV, which will include the latest included video
	$podtube->makeFullFeed();
} // If there is no URL set, then just recreate a feed from the existing items in the CSV
else{
	$podtube = new PodTube($dal, $myURL, $downloadPath);
	// Before we make the feed, check that every file is downloaded
	$items = $dal->getFeed($user);
	for($x=0;$x<$user->getFeedLength() && isset($items[$x]);$x++){
		if(!file_exists($downloadPath.DIRECTORY_SEPARATOR.$items[$x]->getId().".mp3") || !file_exists($downloadPath
				.DIRECTORY_SEPARATOR.$items[$x]->getId().".jpg")){
			$download = new YouTube($items[$x]->getId(), $podtube, $googleAPIServerKey, $downloadPath);
			if(!$download->allDownloaded()){
				$download->downloadThumbnail();
				$download->downloadVideo();
				$download->convert();
			}
		}
	}
	$podtube->makeFullFeed()->printFeed();
}
?>