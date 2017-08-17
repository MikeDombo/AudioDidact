<?php

namespace AudioDidact;
require_once "header.php";
$outputDir = DOWNLOAD_PATH . DIRECTORY_SEPARATOR;

// Set some important constants/ini
ignore_user_abort(true);
ob_implicit_flush(true);

/*
 * Make sure user is logged in, set user variable to the session user.
 */
if(GlobalFunctions::userIsLoggedIn()){
	/** @var \AudioDidact\User $user */
	$user = $_SESSION["user"];
	if(!$user->isEmailVerified() && EMAIL_ENABLED){
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
$dal = GlobalFunctions::getDAL();

// If a video is being requested, then add the video, otherwise just show the current feed
if(isset($_FILES["yt"])){
	if($_FILES["yt"]["error"] > 0){
		echo json_encode(['error' => 'File Upload Error Code:' . $_FILES["yt"]["error"]]);
		exit(1);
	}
	else{
		$extension = pathinfo($_FILES["yt"]["name"])["extension"];
		if($extension != "mp3" && $extension != "mp4"){
			echo json_encode(['error' => 'Unsupported Extension!']);
			exit(1);
		}
		move_uploaded_file($_FILES["yt"]["tmp_name"], $outputDir . $_FILES["yt"]["name"]);
		$generatedID = hash_file("sha256", $outputDir . $_FILES["yt"]["name"]);
		rename($outputDir . $_FILES["yt"]["name"], $outputDir . $generatedID . "." . $extension);

		$thumbnailFilename = $generatedID . ".jpg";
		// Save art from base64 encoded data
		if(mb_strpos($_POST["art"], "data:image") > -1){
			$thumbnailFilename = saveBase64Image($_POST["art"], $generatedID, $outputDir);
		}
		// Save art from URL
		else if(!empty($_POST["art"])){
			$content = file_get_contents($_POST["art"]);
			$fp = fopen($outputDir . $generatedID . ".jpg", "w");
			fwrite($fp, $content);
			fclose($fp);
		}
		// If no art is provided, use "no image available" from wikimedia
		else{
			$content = file_get_contents("https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/No_image_available.svg/300px-No_image_available.svg.png");
			$fp = fopen($outputDir . $generatedID . ".jpg", "w");
			fwrite($fp, $content);
			fclose($fp);
		}

		echo json_encode(['error' => false]);
	}

	$data = ["ID" => $generatedID, "description" => htmlentities($_POST["description"], ENT_HTML5, "UTF-8"),
		"title" => $_POST["title"], "author" => $_POST["author"], "filename" => $_FILES["yt"]["name"],
		"thumbnailFilename" => $thumbnailFilename,
		"duration" => SupportedSites\SupportedSite::getDurationSeconds(DOWNLOAD_PATH . "/" . $generatedID . "." . $extension)];

	$isVideo = false;
	if(isset($_POST["audvid"])){
		$isVideo = boolval($_POST["audvid"]);
	}

	// Try to download all the files, but if an error occurs, do not add the video to the feed
	$download = new SupportedSites\ManualUpload($data, $isVideo);
	$video = $download->getVideo();

	// If not all thumbnail, video, and audio are downloaded, then download them in that order
	if(!$download->allDownloaded()){
		$download->downloadThumbnail();
		$download->downloadVideo();
		if(!$video->isIsVideo()){
			$download->convert();
		}
	}
	if(!$video->isIsVideo()){
		$download->applyArt();
	}
	if(!$dal->inFeed($video, $user)){
		$dal->addVideo($video, $user);
	}
	PodTube::makeFullFeed($user, $dal);
}

/**
 * Saves a given base64 encoded image to a jpg file
 *
 * @param $base64ImageString string base64 encoded image
 * @param $outputFileNoExt string output filename without an extension specified
 * @param $pathEndSlash string path of where to save the file without a trailing slash
 * @return string Full filename that was written into
 */
function saveBase64Image($base64ImageString, $outputFileNoExt, $pathEndSlash){
	$splited = explode(',', mb_substr($base64ImageString, 5), 2);
	$mime = $splited[0];
	$data = $splited[1];
	$outputFileWithExt = $outputFileNoExt;

	$mimeSplitWithoutBase64 = explode(';', $mime, 2);
	$mimeSplit = explode('/', $mimeSplitWithoutBase64[0], 2);
	if(count($mimeSplit) == 2){
		$extension = $mimeSplit[1];
		if($extension == 'jpeg'){
			$extension = 'jpg';
		}
		else if($extension == "png"){
			$extension = "png";
		}

		$outputFileWithExt .= '.' . $extension;
	}
	file_put_contents($pathEndSlash . $outputFileWithExt, base64_decode($data));

	return $outputFileWithExt;
}
