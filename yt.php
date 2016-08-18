<?php
include __DIR__."/header.php";
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

$myDalClass = ChosenDAL;
$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);

// If a video is being requested, then add the video, otherwise just show the current feed
if(isset($_GET["yt"]) || (isset($argv) && isset($argv[2]))){
	if(isset($argv) && isset($argv[2])){
		$_GET["yt"] = $argv[2];
	}
	$podtube = new PodTube($dal, LOCAL_URL, DOWNLOAD_PATH);
	$download = new YouTube($_GET["yt"], $podtube, GOOGLE_API_KEY, DOWNLOAD_PATH);

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
	checkFilesExist($dal, $podtube, $user);

	// Create the RSS feed from the existing CSV, which will include the latest included video
	$podtube->makeFullFeed();
} // If there is no URL set, then just recreate a feed from the existing items in the CSV
else{
	$podtube = new PodTube($dal, LOCAL_URL, DOWNLOAD_PATH);
	// Before we make the feed, check that every file is downloaded
	checkFilesExist($dal, $podtube, $user);
	$podtube->makeFullFeed()->printFeed();
}

function checkFilesExist($dal, $podtube, $user){
	$items = $dal->getFeed($user);
	for($x=0;$x<$user->getFeedLength() && isset($items[$x]);$x++){
		if(!file_exists(DOWNLOAD_PATH.DIRECTORY_SEPARATOR.$items[$x]->getId().".mp3") || !file_exists(DOWNLOAD_PATH
				.DIRECTORY_SEPARATOR.$items[$x]->getId().".jpg")){
			$download = new YouTube($items[$x]->getId(), $podtube, GOOGLE_API_KEY, DOWNLOAD_PATH);
			if(!$download->allDownloaded()){
				$download->downloadThumbnail();
				$download->downloadVideo();
				$download->convert();
			}
		}
	}
}