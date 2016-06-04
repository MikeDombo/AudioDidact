<?php
spl_autoload_register(function($class){
	require_once 'Item.php';
	require_once 'Feed.php';
	require_once 'RSS2.php';
});
date_default_timezone_set('UTC');
use \FeedWriter\RSS2;

class youtube{
	private $csvFilePath = 'feed.csv';
	private $downloadPath = 'temp';
	private $localUrl = "http://example.com/"; // Change to your hostname
	private $googleAPIServerKey = "***********"; // Add server key here
	private $rssFilePath = "rss.xml";
	
	private $thumbnailFilePath = "";
	private $videoFilePath = "";
	private $audioFilePath = "";
	private $descr = "";
	private $videoID = "";
	private $videoTitle = "";
	private $videoAuthor = "";
	private $time;
	
	public function __construct($str=NULL, $instant=FALSE){		
		$this->YT_BASE_URL = "http://www.youtube.com/";

		$this->CURL_UA = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko Firefox/11.0";
		
		if(!file_exists($this->downloadPath)){
			mkdir($this->downloadPath);
		}
		
		if($str != NULL){
			$this->videoID = $this->setYoutubeID($str);
			$this->time = time();
			if(!$this->isInCSV()){
				$info = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet&id=".$this->videoID."&fields=items/snippet/description,items/snippet/title,items/snippet/channelTitle&key=".$this->googleAPIServerKey), true);
				if(!isset($info['items'][0]['snippet'])){
					echo json_encode(['stage'=>-1, 'error'=>"ID Inaccessible", 'progress'=>0]);
					exit(1);
				}
				$info = $info['items'][0]['snippet'];
				$this->videoTitle = utf8_decode($info["title"]);
				$this->videoAuthor = utf8_decode($info["channelTitle"]);
				$this->descr = utf8_decode($info["description"]);
			}
			
			if($instant === TRUE){
				$this->downloadThumbnail();
				$this->downloadVideo();
				$this->convert();
			}
		}
	}
	
	public function getVideoID(){
		return $this->videoID;
	}
	
	public function getCSVFilePath(){
		return $this->csvFilePath;
	}
		
	public function getVideoTitle(){
		return $this->videoTitle;
	}
	
	public function getVideoAuthor(){
		return $this->videoAuthor;
	}
	
	public function getVideoTime(){
		return $this->time;
	}
	
	public function getDescr(){
		return $this->descr;
	}
	
	public function addToCSV(){
		$list = array($this->videoID, utf8_encode($this->videoTitle), utf8_encode($this->videoAuthor), $this->time, utf8_encode($this->descr));
		$handle = fopen($this->csvFilePath, "a");
		fputcsv($handle, [json_encode($list)]);
		fclose($handle);
	}
	
	public function isInCSV(){
		if(file_exists($this->csvFilePath)){
			$csv = array_map('str_getcsv', file($this->csvFilePath));
			$csv = array_reverse($csv);
			foreach($csv as $k=>$v){
				$v = json_decode($v[0], true);
				if($v[0] == $this->videoID){
					$this->videoAuthor = $v[2];
					$this->videoTitle = $v[1];
					$this->videoID = $v[0];
					$this->time = $v[3];
					$this->descr = $v[4];
					return true;
				}
			}
		}
		return false;
	}
	
	public function allDownloaded(){
		$downloadFilePath = $this->downloadPath.DIRECTORY_SEPARATOR.$this->videoID;
		if(!file_exists($downloadFilePath.".jpg")){
			$this->downloadThumbnail();
		}
		if(file_exists($downloadFilePath.".mp3") && file_exists($downloadFilePath.".mp4")){
			if($this->getDuration($downloadFilePath.".mp3")){
				return true;
			}
		}
		if(file_exists($downloadFilePath.".mp4") && $this->getDuration($downloadFilePath.".mp4")){
			$this->convert();
			return true;
		}
		return false;
	}
	
	public function convert(){
		$path = getcwd().DIRECTORY_SEPARATOR.$this->downloadPath.DIRECTORY_SEPARATOR;
		$ffmpeg_infile = $path . $this->videoID .".mp4";
		$ffmpeg_outfile = $path . $this->videoID .".mp3";
		
		$cmd = "ffmpeg -i \"$ffmpeg_infile\" -y -q:a 0 -map a \"$ffmpeg_outfile\" 1> ".$this->videoID.".txt 2>&1";
		pclose(popen("start /B ".$cmd, "r"));
		$progress = 0;
		while($progress != 100){
			$content = @file_get_contents($this->videoID.'.txt');
			preg_match("/Duration: (.*?), start:/", $content, $matches);
			if(!isset($matches[1])){
				usleep(500000);
				continue;
			}
			$rawDuration = $matches[1];
			$ar = array_reverse(explode(":", $rawDuration));
			$duration = floatval($ar[0]);
			if (!empty($ar[1])) $duration += intval($ar[1]) * 60;
			if (!empty($ar[2])) $duration += intval($ar[2]) * 60 * 60;
			preg_match_all("/time=(.*?) bitrate/", $content, $matches);

			$rawTime = array_pop($matches);
			if (is_array($rawTime)){$rawTime = array_pop($rawTime);}
			$ar = array_reverse(explode(":", $rawTime));
			$time = floatval($ar[0]);
			if (!empty($ar[1])) $time += intval($ar[1]) * 60;
			if (!empty($ar[2])) $time += intval($ar[2]) * 60 * 60;
			$progress = round(($time/$duration) * 100);

			$response = array('stage' =>1, 'progress' => $progress);
			echo json_encode($response);
			usleep(500000);
		}
		@unlink($this->videoID.".txt");
		clearstatcache();
	}
	
	public function downloadVideo(){
		$id = $this->videoID;
		$path = getcwd().DIRECTORY_SEPARATOR.$this->downloadPath.DIRECTORY_SEPARATOR;
		$videoFilename = "$id.mp4";
		$video = $path . $videoFilename;
		
		$url = exec("python getYTDownloadURL.py $id");
		echo $url;
		echo "<br/>";
		$downloaded = $this->downloadWithPercentage($url, $video);
		@chmod($video, 0775);
		
		return;
	}
	
	public function downloadThumbnail(){
		$thumbFilename = $this->videoID.".jpg";
		$path = getcwd().DIRECTORY_SEPARATOR.$this->downloadPath.DIRECTORY_SEPARATOR;
		$thumbnail = $path . $thumbFilename;
		$download = file_put_contents($thumbnail, fopen("http://img.youtube.com/vi/".$this->videoID."/mqdefault.jpg", "r"));
		@chmod($thumbnail, 0775);
	}
	
	private function downloadWithPercentage($url, $localfile){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);
		if ($data === false) {
			echo 'cURL failed';
			exit;
		}

		$contentLength = 'unknown';
		if (preg_match_all('/Content-Length: (\d+)/', $data, $matches)) {
			$contentLength = (int)$matches[count($matches)-1][count($matches[count($matches)-1])-1];
		}
		
		if(intval($contentLength)>0){
			$remote = fopen($url, 'r');
			$local = fopen($localfile, 'w');

			$read_bytes = 0;
			while(!feof($remote)) {
				$buffer = fread($remote, 4096);
				fwrite($local, $buffer);
				$read_bytes += 4096;

				$progress = min(100, 100 * $read_bytes / $contentLength);
				$response = array('stage' =>0, 'progress' => $progress);
				echo json_encode($response);
			}
			fclose($remote);
			fclose($local);
		}
		
		return true;
	}
	
	private function curl_httpstatus($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->CURL_UA);
		curl_setopt($ch, CURLOPT_REFERER, $this->YT_BASE_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$str = curl_exec($ch);
		$int = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return intval($int);
	}
	
	private function setYoutubeID($str){
		$tmp_id = self::parse_yturl($str);
		$vid_id = ($tmp_id !== FALSE) ? $tmp_id : $str;
		$url = sprintf($this->YT_BASE_URL . "watch?v=%s", $vid_id);
		if(self::curl_httpstatus($url) !== 200 && self::curl_httpstatus($url) !== 301){
			throw new Exception("Invalid Youtube video ID: $vid_id");
			exit();
		}
		return $vid_id;
	}
	
	public function makeFullFeed(){
		$fe = $this->makeFeed();
		if(file_exists($this->csvFilePath) && count(file($this->csvFilePath))>50){
			$this->deleteLast($this->csvFilePath);
		}
		
		if(file_exists($this->csvFilePath)){
			$csv = array_map('str_getcsv', file($this->csvFilePath));
			$csv = array_reverse($csv);
			foreach($csv as $k=>$v){
				$v = json_decode($v[0], true);
				$author = $v[2];
				$title = $v[1];
				$id = $v[0];
				$time = $v[3];
				$descr = $v[4];
				$fe = $this->addFeedItem($fe, $title, $id, $author, $time, $descr);
			}
		}
		
		file_put_contents($this->rssFilePath, $fe->generateFeed());
		return $fe;
	}

	private function deleteLast($file){
		$downloadPath = $this->downloadPath;
		$id = $this->videoID;
		if(file_exists($file)){
			$csv = array_map('str_getcsv', file($file));
			foreach($csv as $k=>$v){
				$v = json_decode($v[0], true);
				$author = $v[2];
				$title = $v[1];
				$id = $v[0];
				$time = $v[3];
				$descr = $v[4];
				if($k == 0){
					@unlink($downloadPath.DIRECTORY_SEPARATOR.$id.".mp3");
					@unlink($downloadPath.DIRECTORY_SEPARATOR.$id.".mp4");
					@unlink($downloadPath.DIRECTORY_SEPARATOR.$id.".jpg");
					continue;
				}
				$handle = fopen($file.".tmp", "a");
				fputcsv($handle, [json_encode([$id, $title, $author, $time, $descr])]);
				fclose($handle);
			}
			@unlink($file);
			@rename($file.".tmp", $file);
		}
	}

	private function makeFeed(){		
		$feed = new RSS2;
		$feed->setTitle('YouTube to Podcast');
		$feed->setLink($this->localUrl);
		$feed->setDescription('Converts YouTube videos into a podcast feed.');
		$feed->setImage('YouTube to Podcast', $this->localUrl, 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Rss-feed.svg/256px-Rss-feed.svg.png');
		$feed->setChannelElement('itunes:image', "", array('href'=>'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Rss-feed.svg/256px-Rss-feed.svg.png'));
		$feed->setChannelElement('language', 'en-US');
		$feed->setDate(date(DATE_RSS, time()));
		$feed->setChannelElement('pubDate', date(\DATE_RSS, time()));
		$feed->setSelfLink($this->localUrl.$this->rssFilePath);
		$feed->addNamespace("media", "http://search.yahoo.com/mrss/");
		$feed->addNamespace("itunes", "http://www.itunes.com/dtds/podcast-1.0.dtd");
		$feed->addNamespace("content", "http://purl.org/rss/1.0/modules/content/");
		$feed->addNamespace("sy", "http://purl.org/rss/1.0/modules/syndication/");
		
		$feed->setChannelElement('itunes:explicit', "yes");
		$feed->setChannelElement('itunes:category', "",array('text'=>'Technology'));
		$feed->setChannelElement('sy:updatePeriod', "Hourly");
		$feed->setChannelElement('sy:updateFrequency', "1");
		$feed->setChannelElement('ttl', "1");
		return $feed;
	}

	private function addFeedItem($feed, $title, $id, $author, $time, $descr){
		$newItem = $feed->createNewItem();
		
		// Make description links clickable
		$descr = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $descr);
		
		$duration = $this->getDuration($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3");
		
		$newItem->setTitle($title);
		$newItem->setLink("http://youtube.com/watch?v=".$id);
		$newItem->setDescription("<h1>$title</h1><h2>$author</h2><p><img class=\"alignleft size-medium\" src='".$this->localUrl.$this->downloadPath."/".$id.".jpg' alt=\"".$title." -- ".$author."\" width=\"300\" height=\"170\" /></p><p>$descr</p>");
		$newItem->addElement('media:content', array('media:title'=>$title), array('fileSize'=>filesize($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3"), 'type'=> 'audio/mp3', 'medium'=>'audio', 'url'=>$this->localUrl.$this->downloadPath."/".$id.'.mp3'));
		$newItem->addElement('itunes:image', "", array('href'=>$this->localUrl.$this->downloadPath."/".$id.'.jpg'));
		$newItem->addElement('itunes:author', $author);
		$newItem->addElement('itunes:duration', $duration);

		$newItem->setDate(date(DATE_RSS,$time));

		$newItem->setEnclosure($this->localUrl.$this->downloadPath."/".$id.".mp3", filesize($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3"), 'audio/mp3');

		$newItem->setAuthor($author, 'me@me.com');
		
		$newItem->setId($this->localUrl.$this->downloadPath."/".$id.".mp3", true);

		$feed->addItem($newItem);
		$myFeed = $feed->generateFeed();
		return $feed;
	}
	
	private function getDuration($file){
		$dur = shell_exec("ffmpeg.exe -i ".$file." 2>&1");
		if(preg_match("/: Invalid /", $dur)){
			return false;
		}
		preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $dur, $duration);
		$duration = $duration[1].":".$duration[2].":".$duration[3];
		return $duration;
	}
	
	private function parse_yturl($url){
		$pattern = '#^(?:https?://)?';    # Optional URL scheme. Either http or https.
		$pattern .= '(?:www\.)?';         #  Optional www subdomain.
		$pattern .= '(?:';                #  Group host alternatives:
		$pattern .=   'youtu\.be/';       #    Either youtu.be,
		$pattern .=   '|youtube\.com';    #    or youtube.com
		$pattern .=   '(?:';              #    Group path alternatives:
		$pattern .=     '/embed/';        #      Either /embed/,
		$pattern .=     '|/v/';           #      or /v/,
		$pattern .=     '|/watch\?v=';    #      or /watch?v=,
		$pattern .=     '|/watch\?.+&v='; #      or /watch?other_param&v=
		$pattern .=   ')';                #    End path alternatives.
		$pattern .= ')';                  #  End host alternatives.
		$pattern .= '([\w-]{11})';        # 11 characters (Length of Youtube video ids).
		$pattern .= '(?:.+)?$#x';         # Optional other ending URL parameters.
		preg_match($pattern, $url, $matches);
		return (isset($matches[1])) ? $matches[1] : FALSE;
	}
}
?>