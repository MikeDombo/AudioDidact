<?php
require_once __DIR__."/header.php";

$myDalClass = ChosenDAL;
$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
$pruneVids = $dal->getPrunableVideos();
foreach($pruneVids as $v){
	$downloadPath = DOWNLOAD_PATH.DIRECTORY_SEPARATOR.$v;
	@unlink($downloadPath.".mp3");
	@unlink($downloadPath.".mp4");
	@unlink($downloadPath.".jpg");
	echo "Unsetting ".$downloadPath."<br/>";
}
