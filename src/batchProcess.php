<?php
require_once __DIR__ . "/header.php";

$dal = getDAL();
$pruneVids = $dal->getPrunableVideos();
foreach($pruneVids as $v){
	$downloadPath = DOWNLOAD_PATH . DIRECTORY_SEPARATOR . $v;
	@unlink($downloadPath . ".mp3");
	@unlink($downloadPath . ".mp4");
	@unlink($downloadPath . ".jpg");
	echo "Unsetting " . $downloadPath . "<br/>";
}
