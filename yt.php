<?php
spl_autoload_register(function($class){
	require_once 'youtube.php';
	require_once 'PodTube.php';
});

ignore_user_abort(true);
ini_set('max_execution_time', 0);
ob_implicit_flush(true);


$myURL = "http://example.com/"; // Change to your hostname
$googleAPIServerKey = "*********"; // Add server key here

$downloadPath = "temp";
$csvFile = "feed.csv";
$rssFile = "rss.xml";


if(isset($_GET["yt"])){
	$podtube = new PodTube($rssFile, $csvFile, $myURL, $downloadPath);
	$download = new YouTube($_GET["yt"], $podtube, $googleAPIServerKey, $downloadPath);

	// If the video is not yet in the CSV file, add it
	if(!$podtube->isInCSV($download->getVideoID())){
		$podtube->addToCSV($download->getVideoID(), $download->getVideoTitle(),
			$download->getVideoAuthor(), $download->getVideoTime(), $download->getDescr());
	}

	// If not all thumbnail, video, and audio are downloaded, then download them in that order
	if(!$download->allDownloaded()){
		$download->downloadThumbnail();
		$download->downloadVideo();
		$download->convert();
	}

	// Create the RSS feed from the existing CSV, which will include the latest included video
	$podtube->makeFullFeed();
} // If there is no URL set, then just recreate a feed from the existing items in the CSV
else{
	$podtube = new PodTube($rssFile, $csvFile, $myURL, $downloadPath);
	$podtube->makeFullFeed()->printFeed();
}
?>