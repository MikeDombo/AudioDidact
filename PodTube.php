<?php
// Include RSS feed generation library
spl_autoload_register(function(){
	require_once 'Feeds/Item.php';
	require_once 'Feeds/Feed.php';
	require_once 'Feeds/RSS2.php';
});
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");
use \FeedWriter\RSS2;

class PodTube{
	private $rssFilePath = "";
	private $csvFilePath = "";
	private $localUrl = "";
	private $downloadPath = "";

	public function __construct($rssFilePath="rss.xml", $csvFilePath="feed.csv", $localURL=NULL, $downloadPath="temp"){
		$this->downloadPath = $downloadPath;
		$this->rssFilePath = $rssFilePath;
		$this->csvFilePath = $csvFilePath;
		$this->localUrl = $localURL;
	}

	// Adds the current video to the CSV file
	public function addToCSV($videoID, $videoTitle, $videoAuthor, $time, $descr){
		if($this->isInCSV($videoID)){
			return;
		}
		$list = [$videoID, utf8_encode($videoTitle), utf8_encode($videoAuthor), $time, utf8_encode($descr)];
		$handle = fopen($this->csvFilePath, "a");
		fputcsv($handle, [json_encode($list)]);
		fclose($handle);
	}

	// Delete any videos in the CSV over 50 videos in reverse-chronological order
	public function deleteLast($file){
		$downloadPath = $this->downloadPath;
		if(file_exists($file)){
			$csv = array_map('str_getcsv', file($file));
			$csv = array_reverse($csv, false);
			$newCSV = [];
			foreach($csv as $k=>$v){
				$v = json_decode($v[0], true);
				$author = utf8_decode($v[2]);
				$title = utf8_decode($v[1]);
				$id = $v[0];
				$time = $v[3];
				$descr = utf8_decode($v[4]);
				if($k<50){
					$newCSV[$k] = $v;
				}
				else{
					@unlink($downloadPath.DIRECTORY_SEPARATOR.$id.".mp3");
					@unlink($downloadPath.DIRECTORY_SEPARATOR.$id.".mp4");
					@unlink($downloadPath.DIRECTORY_SEPARATOR.$id.".jpg");
					continue;
				}
				
			}
			$newCSV = array_reverse($newCSV);
			$handle = fopen($file.".tmp", "a");
			foreach($newCSV as $v){
				$author = utf8_decode($v[2]);
				$title = utf8_decode($v[1]);
				$id = $v[0];
				$time = $v[3];
				$descr = utf8_decode($v[4]);
				fputcsv($handle, [json_encode([$id, utf8_encode($title), utf8_encode($author), $time, utf8_encode($descr)])]);
			}
			fclose($handle);
			@unlink($file); // Delete the existing CSV
			@rename($file.".tmp", $file); // Rename the temporary CSV to the same name as the real CSV
		}
	}

	// Checks if the current video is in the CSV file
	public function isInCSV($videoID){
		if(file_exists($this->csvFilePath)){
			$csv = array_map('str_getcsv', file($this->csvFilePath));
			$csv = array_reverse($csv);
			foreach($csv as $k=>$v){
				$v = json_decode($v[0], true);
				// If the video ID is in the CSV, then return true
				if($v[0] == $videoID){
					return true;
				}
			}
		}
		return false;
	}

	public function getDataFromCSV($videoID){
		if(file_exists($this->csvFilePath)){
			$csv = array_map('str_getcsv', file($this->csvFilePath));
			$csv = array_reverse($csv);
			foreach($csv as $k=>$v){
				$v = json_decode($v[0], true);
				// If the video ID is in the CSV, then return true
				if($v[0] == $videoID){
					return ["id"=>$v[0], "title"=>$v[1], "author"=>$v[2], "time"=>$v[3], "description"=>$v[4]];
				}
			}
		}
		return false;
	}

	// Make the RSS feed from the CSV file
	public function makeFullFeed(){
		// Setup global feed values
		$fe = $this->makeFeed();
		// Prune the CSV if there are more than 50 items
		if(file_exists($this->csvFilePath) && count(file($this->csvFilePath))>50){
			$this->deleteLast($this->csvFilePath);
		}

		// Use the CSV to add each video to the feed in reverse-chronological order
		if(file_exists($this->csvFilePath)){
			$csv = array_map('str_getcsv', file($this->csvFilePath));
			$csv = array_reverse($csv);
			foreach($csv as $k=>$v){
				$v = json_decode($v[0], true);
				$author = utf8_decode($v[2]);
				$title = utf8_decode($v[1]);
				$id = $v[0];
				$time = $v[3];
				$descr = utf8_decode($v[4]);
				$fe = $this->addFeedItem($fe, $title, $id, $author, $time, $descr);
			}
		}

		// Save the generated feed to the rssFilePath
		file_put_contents($this->rssFilePath, $fe->generateFeed());
		return $fe;
	}


	// Generate the global feed header variables
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
		$feed->setChannelElement('itunes:author', "Michael Dombrowski");
		$feed->setChannelElement('itunes:category', "",array('text'=>'Technology'));
		$feed->setChannelElement('sy:updatePeriod', "Hourly");
		$feed->setChannelElement('sy:updateFrequency', "1");
		$feed->setChannelElement('ttl', "1");
		return $feed;
	}

	// Add an item to the RSS feed
	private function addFeedItem($feed, $title, $id, $author, $time, $descr){
		$newItem = $feed->createNewItem();

		// Make description links clickable
		$descr = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $descr);
		$descr = nl2br($descr);
		// Get the duration of the video and use it for the itunes:duration tag
		$duration = YouTube::getDuration($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3");

		$newItem->setTitle($title);
		$newItem->setLink("http://youtube.com/watch?v=".$id);
		// Set description to be the title, author, thumbnail, and then the original video description
		$newItem->setDescription("<h1>$title</h1><h2>$author</h2><p><img class=\"alignleft size-medium\" src='".$this->localUrl.$this->downloadPath."/".$id.".jpg' alt=\"".$title." -- ".$author."\" width=\"300\" height=\"170\" /></p><p>$descr</p>");
		$newItem->addElement('media:content', array('media:title'=>$title), array('fileSize'=>filesize($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3"), 'type'=> 'audio/mp3', 'medium'=>'audio', 'url'=>$this->localUrl.$this->downloadPath."/".$id.'.mp3'));
		$newItem->setEnclosure($this->localUrl.$this->downloadPath."/".$id.".mp3", filesize($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3"), 'audio/mp3');
		$newItem->addElement('itunes:image', "", array('href'=>$this->localUrl.$this->downloadPath."/".$id.'.jpg'));
		$newItem->addElement('itunes:author', $author);
		$newItem->addElement('itunes:duration', $duration);

		$newItem->setDate(date(DATE_RSS,$time));
		$newItem->setAuthor($author, 'me@me.com');
		$newItem->setId($this->localUrl.$this->downloadPath."/".$id.".mp3", true); // Set GUID, this is absolutely necessary

		$feed->addItem($newItem); // Add the item generated to the global feed
		return $feed;
	}

	public function getDownloadPath(){
		return $this->downloadPath;
	}

	public function getCSVFilePath(){
		return $this->csvFilePath;
	}

	public function getRssPath(){
		return $this->rssFilePath;
	}
}
