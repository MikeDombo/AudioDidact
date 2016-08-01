<?php
// Include RSS feed generation library
spl_autoload_register(function(){
	require_once 'Feeds/Item.php';
	require_once 'Feeds/Feed.php';
	require_once 'Feeds/RSS2.php';
	require_once 'classes/DAL.php';
	require_once 'classes/MySQLDAL.php';
	require_once 'classes/Video.php';
	require_once 'classes/User.php';
});

date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");
use \FeedWriter\RSS2;
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

class PodTube{
	private $rssFilePath = "";
	private $localUrl = "";
	private $downloadPath = "";
	private $dal;
	private $user;

	public function __construct($dal, $rssFilePath="rss.xml", $localURL=NULL,
	                            $downloadPath="temp"){
		$this->dal = $dal;
		$this->downloadPath = $downloadPath;
		$this->rssFilePath = $rssFilePath;
		$this->localUrl = $localURL;
		$this->user = $_SESSION["user"];
	}

	public function inFeed($id){
		$video = new Video();
		$video->setId($id);
		return $this->dal->inFeed($video, $this->user);
	}

	public function getDataFromFeed($id){
		$items = $this->dal->getFeed($this->user);
		foreach($items as $i){
			if($i->getId() == $id){
				return $i;
			}
		}
		return false;
	}

	// Make the RSS feed from the CSV file
	public function makeFullFeed(){
		// Setup global feed values
		$fe = $this->makeFeed();

		$items = $this->dal->getFeed($this->user);
		for($x=0;$x<50 && isset($items[$x]);$x++){
			$i = $items[$x];
			$fe = $this->addFeedItem($fe, $i->getTitle(), $i->getId(), $i->getAuthor(), $i->getTime(), $i->getDesc());
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
