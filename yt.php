<?php
require_once __DIR__."/header.php";
// Set some important constants/ini
ignore_user_abort(true);
ob_implicit_flush(true);

/*
 * Make sure user is logged in, set user variable to the session user.
 */
if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && isset($_SESSION["user"]) && $_SESSION["user"] instanceof
	User){
	$user = $_SESSION["user"];
}
else{
	echo json_encode(['stage'=>-1, 'error'=>"Must be logged in to continue!", 'progress'=>0]);
	exit(1);
}
// Write session to file to prevent concurrency issues
session_write_close();

$myDalClass = ChosenDAL;
$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);

// If a video is being requested, then add the video, otherwise just show the current feed
if(isset($_GET["yt"]) || (isset($argv) && isset($argv[2]))){
	if(isset($argv) && isset($argv[2])){
		$_GET["yt"] = $argv[2];
	}
	$url = ($_GET["yt"]);
	$podtube = new PodTube($dal, $user);

	// Try to download all the files, but if an error occurs, do not add the video to the feed
	try{
		$download = routeByURL($url, $url, $podtube);
		$video = $download->getVideo();

		// If not all thumbnail, video, and audio are downloaded, then download them in that order
		if(!$download->allDownloaded()){
			$download->downloadThumbnail();
			$download->downloadVideo();
			$download->convert();
		}

		if(!$dal->inFeed($video, $user)){
			$dal->addVideo($video, $user);
		}
	}
	catch(Exception $e){
		exit();
	}

	// Before we make the feed, check that every file is downloaded
	checkFilesExist($dal, $podtube, $user);

	// Create the RSS feed from the existing CSV, which will include the latest included video
	$podtube->makeFullFeed();
}
// If there is no URL set, then just recreate a feed from the existing items in the CSV
else{
	$podtube = new PodTube($dal, $user);
	// Before we make the feed, check that every file is downloaded
	checkFilesExist($dal, $podtube, $user);
	$podtube->makeFullFeed()->printFeed();
}

/**
 * Gets the list of all feed items and makes sure that all of them are downloaded and available
 *
 * @param DAL $dal
 * @param PodTube $podTube
 * @param User $user
 */
function checkFilesExist(DAL $dal, PodTube $podTube, User $user){
	$items = $dal->getFeed($user);
	for($x=0; $x<$user->getFeedLength() && isset($items[$x]); $x++){
		if(!file_exists(DOWNLOAD_PATH.DIRECTORY_SEPARATOR.$items[$x]->getId().".mp3") || !file_exists(DOWNLOAD_PATH
				.DIRECTORY_SEPARATOR.$items[$x]->getId().".jpg")){
			$download = routeByURL($items[$x]->getURL(), $items[$x]->getId(), $podTube);
			if($download != null){
				if(!$download->allDownloaded()){
					$download->downloadThumbnail();
					$download->downloadVideo();
					$download->convert();
				}
			}
		}
	}
}

/**
 * @param $url
 * @param $id
 * @param $podTube
 * @return \SupportedSite
 */
function routeByURL($url, $id, $podTube){
	if(strpos($url, "youtube") > -1 || strpos($url, "youtu.be") > -1){
		return new YouTube($id, $podTube);
	}
	else if(strpos($url, "crtv.com") > -1){
		return new CRTV($id, $podTube);
	}
	else if(strpos($url, "soundcloud.com") > -1){
		return new SoundCloud($id, $podTube);
	}
	else {
		error_log("Could not find route for URL: ".$url." or ID: ".$id);
	}
}

