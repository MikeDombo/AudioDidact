<?php
require_once "header.php";
$output_dir = DOWNLOAD_PATH.DIRECTORY_SEPARATOR;

// Set some important constants/ini
ignore_user_abort(true);
ob_implicit_flush(true);

/*
 * Make sure user is logged in, set user variable to the session user.
 */
if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && isset($_SESSION["user"]) && $_SESSION["user"] instanceof
	User){
	$user = $_SESSION["user"];
	if(!$user->isEmailVerified()){
		echo json_encode(['stage'=>-1, 'error'=>"Must verify email first!", 'progress'=>0]);
		exit(1);
	}
}
else{
	echo json_encode(['stage'=>-1, 'error'=>"Must be logged in to continue!", 'progress'=>0]);
	exit(1);
}
// Write session to file to prevent concurrency issues
session_write_close();

$myDalClass = ChosenDAL;
/** @var $dal \DAL */
$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);

// If a video is being requested, then add the video, otherwise just show the current feed
if(isset($_FILES["yt"])){
	if ($_FILES["yt"]["error"] > 0)
	{
		echo json_encode(['error'=>'File Upload Error Code:'.$_FILES["yt"]["error"]]);
		exit(1);
	}
	else{
		$extension = pathinfo($_FILES["yt"]["name"])["extension"];
		if($extension != "mp3" && $extension != "mp4"){
			echo json_encode(['error'=>'Unsupported Extension!']);
			exit(1);
		}
		move_uploaded_file($_FILES["yt"]["tmp_name"], $output_dir.$_FILES["yt"]["name"]);
		$generatedID = hash_file("sha256", $output_dir.$_FILES["yt"]["name"]);
		rename($output_dir.$_FILES["yt"]["name"], $output_dir.$generatedID.".".$extension);

		// Save art from base64 encoded data
		if(strpos($_POST["art"], "data:image") > -1){
			save_base64_image($_POST["art"], $generatedID, $output_dir);
		}
		// Save art from URL
		else{
			$content = file_get_contents($_POST["art"]);
			$fp = fopen($output_dir.$generatedID.".jpg", "w");
			fwrite($fp, $content);
			fclose($fp);
		}

		echo json_encode(['error'=>false]);
	}

	$data = ["ID"=>$generatedID, "description"=>htmlentities($_POST["description"], ENT_HTML5, "UTF-8"),
			"title"=>$_POST["title"], "author"=>$_POST["author"], "filename"=>$_FILES["yt"]["name"]];

	$podtube = new PodTube($dal, $user);

	// Try to download all the files, but if an error occurs, do not add the video to the feed
	$download = new ManualUpload($data, $podtube);
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
	$podtube->makeFullFeed();
}

/**
 * Saves a given base64 encoded image to a jpg file
 * @param $base64_image_string string base64 encoded image
 * @param $output_file_without_ext string output filename without an extension specified
 * @param $path_with_end_slash string path of where to save the file without a trailing slash
 * @return string Full filename that was written into
 */
function save_base64_image($base64_image_string, $output_file_without_ext, $path_with_end_slash) {
	$splited = explode(',', substr($base64_image_string, 5), 2);
	$mime = $splited[0];
	$data = $splited[1];

	$mime_split_without_base64 = explode(';', $mime, 2);
	$mime_split = explode('/', $mime_split_without_base64[0], 2);
	if(count($mime_split) == 2){
		$output_file_without_ext .= '.jpg';
	}
	file_put_contents($path_with_end_slash.$output_file_without_ext, base64_decode($data));
	return $output_file_without_ext;
}
