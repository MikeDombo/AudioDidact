<?php

namespace AudioDidact;
use AudioDidact\DB\DAL;
use AudioDidact\SupportedSites\SupportedSite;

require_once __DIR__ . "/header.php";

// Set some important constants/ini
ignore_user_abort(true);
ob_implicit_flush(true);

/*
 * Make sure user is logged in, set user variable to the session user.
 */
if(userIsLoggedIn()){
	/** @var \AudioDidact\User $user */
	$user = $_SESSION["user"];
	if(!$user->isEmailVerified()){
		echo json_encode(['stage' => -1, 'error' => "Must verify email first!", 'progress' => 0]);
		exit(1);
	}
}
else{
	echo json_encode(['stage' => -1, 'error' => "Must be logged in to continue!", 'progress' => 0]);
	exit(1);
}
// Write session to file to prevent concurrency issues
session_write_close();
$dal = getDAL();

// If a video is being requested, then add the video, otherwise just show the current feed
if(isset($_GET["yt"])){
	$url = ($_GET["yt"]);
	$isVideo = false;

	if(isset($_GET["videoOnly"]) && $_GET["videoOnly"] == "true"){
		$isVideo = true;
	}

	// Try to download all the files, but if an error occurs, do not add the video to the feed
	try{
		$download = getSupportedSiteClass($url, $url, $isVideo);
		if($download != null){
			$video = $download->getVideo();

			// If not all thumbnail, video, and audio are downloaded, then download them in that order
			if(!$download->allDownloaded()){
				$download->downloadVideo();
				$download->downloadThumbnail();
				if(!$video->isIsVideo()){
					$download->convert();
					$download->applyArt();
				}
			}

			if(!$dal->inFeed($video, $user)){
				$dal->addVideo($video, $user);
			}
			else{
				$dal->updateVideo($video, $user);
			}
		}
	}
	catch(\Exception $e){
		SupportedSite::echoErrorJSON($e->getMessage());
		exit();
	}

	// Before we make the feed, check that every file is downloaded
	checkFilesExist($dal, $user);
	PodTube::makeFullFeed($user, $dal);
}
// If there is no URL set, then just recreate a feed from the existing items in the CSV
else{
	// Before we make the feed, check that every file is downloaded
	checkFilesExist($dal, $user);
	PodTube::makeFullFeed($user, $dal)->printFeed();
}

/**
 * Gets the list of all feed items and makes sure that all of them are downloaded and available
 *
 * @param \AudioDidact\DB\DAL $dal
 * @param \AudioDidact\User $user
 */
function checkFilesExist(DAL $dal, User $user){
	/** @var array|Video $items */
	$items = $dal->getFeed($user);
	foreach($items as $video){
		if(!file_exists(DOWNLOAD_PATH . DIRECTORY_SEPARATOR . $video->getId() . ".mp3") || !file_exists(DOWNLOAD_PATH
				. DIRECTORY_SEPARATOR . $video->getId() . ".jpg")
		){

			$download = getSupportedSiteClass($video->getURL(), $video->getId(), $video->isIsVideo());
			if($download != null){
				if(!$download->allDownloaded()){
					$download->downloadThumbnail();
					$download->downloadVideo();
					if(!$video->isIsVideo()){
						$download->convert();
					}
				}
			}
		}
	}
}

/**
 * Returns the appropriate SupportedClass to redownload any given content
 *
 * @param $url
 * @param $id
 * @param boolean $isVideo
 * @return \AudioDidact\SupportedSites\SupportedSite
 * @throws \Exception
 */
function getSupportedSiteClass($url, $id, $isVideo){
	try{
		if(mb_strpos($url, "youtube") > -1 || strpos($url, "youtu.be") > -1){
			return new SupportedSites\YouTube($id, $isVideo);
		}
		else if(mb_strpos($url, "crtv.com") > -1){
			return new SupportedSites\CRTV($url, $isVideo);
		}
		else if(mb_strpos($url, "soundcloud.com") > -1){
			return new SupportedSites\SoundCloud($url, $isVideo);
		}
		else if(mb_strpos($url, "vimeo.com") > -1){
			return new SupportedSites\Vimeo($url, $isVideo);
		}
		else if(mb_strlen($id) == 64){
			return null;
		}
		else{
			error_log("Could not find route for URL: " . $url . " or ID: " . $id);
		}
	}
	catch(\Exception $e){
		throw $e;
	}

	echo json_encode(['stage' => -1, 'error' => "Could not find a class to download from that URL.", 'progress' => 0]);

	// Error case or manually uploaded content case
	return null;
}

