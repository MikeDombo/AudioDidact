<?php
spl_autoload_register(function($class){
	require_once 'youtube.php';
});

ignore_user_abort(true);
ini_set('max_execution_time', 0);
ob_implicit_flush(true);

if(isset($_GET["yt"])){
	$download = new youtube($_GET["yt"]);
	if(!$download->allDownloaded()){
		$download->downloadThumbnail();
		$download->downloadVideo();
		$download->convert();
	}
	if(!$download->isInCSV()){
		$download->addToCSV();
	}
	$download->makeFullFeed();
}
else{
	$download = new youtube();
	$download->makeFullFeed()->printFeed();
}
?>