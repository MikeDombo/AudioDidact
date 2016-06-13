<?php
spl_autoload_register(function($class){
	require_once 'youtube.php';
});

ignore_user_abort(true);
ini_set('max_execution_time', 0);
ob_implicit_flush(true);

if(isset($_GET["yt"])){
	$download = new youtube($_GET["yt"]);
	// If not all thumbnail, video, and audio are downloaded, then download them in that order
	if(!$download->allDownloaded()){
		$download->downloadThumbnail();
		$download->downloadVideo();
		$download->convert();
	}
	// If the video is not yet in the CSV file, add it
	if(!$download->isInCSV()){
		$download->addToCSV();
	}
	// Create the RSS feed from the existing CSV, which will include the latest included video
	$download->makeFullFeed();
} // If there is no URL set, then just recreate a feed from the existing items in the CSV
else{
	$download = new youtube();
	$download->makeFullFeed()->printFeed();
}
?>