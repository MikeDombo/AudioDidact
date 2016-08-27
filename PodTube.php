<?php
// Include RSS feed generation library and other classes that are used.
require_once __DIR__."/header.php";
use \FeedWriter\RSS2;

/**
 * Class PodTube
 */
class PodTube{
	/** @var string The local URL of the server */
	private $localUrl = "";
	/** @var string The subdirectory of the local URL where downloaded files are saved */
	private $downloadPath = "";
	/** @var \DAL the DAL */
	private $dal;
	/** @var \User the user */
	private $user;

	/**
	 * PodTube constructor.
	 *
	 * @param \DAL $dal
	 * @param null $localURL
	 * @param string $downloadPath
	 */
	public function __construct(DAL $dal, $localURL, $downloadPath="temp"){
		$this->dal = $dal;
		$this->downloadPath = $downloadPath;
		$this->localUrl = $localURL;

		// This may need to change in future because it is a bit dangerous. PodTube class should only be called when
		// there is a valid user.
		$this->user = $_SESSION["user"];
	}

	/**
	 * Public function exposing database inFeed function.
	 * @param $id
	 * @return bool
	 */
	public function inFeed($id){
		$video = new Video();
		$video->setId($id);
		return $this->dal->inFeed($video, $this->user);
	}

	/**
	 * Returns a Video object from the feed based on the video's id.
	 * @param $id
	 * @return bool
	 */
	public function getDataFromFeed($id){
		$items = $this->dal->getFeed($this->user);
		foreach($items as $i){
			if($i->getId() == $id){
				return $i;
			}
		}
		return false;
	}

	/**
	 * Make the RSS feed from the database
	 * @return \FeedWriter\RSS2|mixed
	 */
	public function makeFullFeed(){
		// Setup global feed values
		$fe = $this->makeFeed();

		$items = $this->dal->getFeed($this->user);
		for($x=0;$x<$this->user->getFeedLength() && isset($items[$x]);$x++){
			$i = $items[$x];
			$fe = $this->addFeedItem($fe, $i->getTitle(), $i->getId(), $i->getAuthor(), $i->getTime(), $i->getDesc());
		}

		// Save the generated feed to the db
		$this->dal->setFeedText($this->user, $fe->generateFeed());
		return $fe;
	}

	/**
	 * Generate the global feed header variables
	 * @return \FeedWriter\RSS2
	 */
	private function makeFeed(){
		// In future Title, Description, Image, and Author should be customizable.

		$feed = new RSS2;
		$feed->setTitle('YouTube to Podcast');
		$feed->setLink($this->localUrl);
		$feed->setDescription('Converts YouTube videos into a podcast feed.');
		$feed->setImage('YouTube to Podcast', $this->localUrl, 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Rss-feed.svg/256px-Rss-feed.svg.png');
		$feed->setChannelElement('itunes:image', "", array('href'=>'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Rss-feed.svg/256px-Rss-feed.svg.png'));
		$feed->setChannelElement('language', 'en-US');
		$feed->setDate(date(DATE_RSS, time()));
		$feed->setChannelElement('pubDate', date(\DATE_RSS, time()));
		$feed->setSelfLink($this->localUrl."user/".$this->user->getWebID()."/feed/");
		$feed->addNamespace("media", "http://search.yahoo.com/mrss/");
		$feed->addNamespace("itunes", "http://www.itunes.com/dtds/podcast-1.0.dtd");
		$feed->addNamespace("content", "http://purl.org/rss/1.0/modules/content/");
		$feed->addNamespace("sy", "http://purl.org/rss/1.0/modules/syndication/");

		$feed->setChannelElement('itunes:explicit', "yes");
		$feed->setChannelElement('itunes:author', "Michael Dombrowski");
		$feed->setChannelElement('itunes:category', "",array('text'=>'Technology'));
		$feed->setChannelElement('sy:updatePeriod', "hourly");
		$feed->setChannelElement('sy:updateFrequency', "1");
		$feed->setChannelElement('ttl', "15");
		return $feed;
	}

	/**
	 * Add an item to the RSS feed
	 * @param $feed
	 * @param $title
	 * @param $id
	 * @param $author
	 * @param $time
	 * @param $descr
	 * @return mixed
	 */
	private function addFeedItem($feed, $title, $id, $author, $time, $descr){
		$newItem = $feed->createNewItem();

		// Make description links clickable
		$descr = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~\@]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $descr);
		$descr = nl2br($descr);

		// Get the duration of the video and use it for the itunes:duration tag
		$duration = YouTube::getDuration($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3");

		$newItem->setTitle($title);
		$newItem->setLink("http://youtube.com/watch?v=".$id);
		// Set description to be the title, author, thumbnail, and then the original video description
		$newItem->setDescription("<h1>$title</h1><h2>$author</h2><p><img class=\"alignleft size-medium\" src='".$this->localUrl.$this->downloadPath."/".$id.".jpg' alt=\"".$title." -- ".$author."\" width=\"300\" height=\"170\" /></p><p>$descr</p>");
		$newItem->addElement('media:content', array('media:title'=>$title), array('fileSize'=>filesize($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3"), 'type'=> 'audio/mp3', 'medium'=>'audio', 'url'=>$this->localUrl.$this->downloadPath."/".$id.'.mp3'));
		$newItem->addElement('media:content', array('media:title'=>$title), array('medium'=>'image',
			'url'=>$this->localUrl.$this->downloadPath."/".$id.'.jpg'), false, true);
		$newItem->setEnclosure($this->localUrl.$this->downloadPath."/".$id.".mp3", filesize($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3"), 'audio/mp3');
		$newItem->addElement('itunes:image', "", array('href'=>$this->localUrl.$this->downloadPath."/".$id.'.jpg'));
		$newItem->addElement('itunes:author', $author);
		$newItem->addElement('itunes:duration', $duration);

		$newItem->setDate(date(DATE_RSS,$time));
		$newItem->setAuthor($author, 'me@me.com');
		// Set GUID, this is absolutely necessary
		$newItem->setId($this->localUrl.$this->downloadPath."/".$id.".mp3", true);

		// Add the item generated to the global feed
		$feed->addItem($newItem);
		return $feed;
	}
}
