<?php
// Include RSS feed generation library and other classes that are used.
require_once __DIR__."/header.php";
use \FeedWriter\RSS2;

/**
 * Class PodTube
 */
class PodTube{
	/** @var DAL the DAL */
	private $dal;
	/** @var User the user */
	private $user;

	/**
	 * PodTube constructor.
	 *
	 * @param DAL $dal
	 * @param \User $user Current user
	 */
	public function __construct(DAL $dal, User $user){
		$this->dal = $dal;

		// This may need to change in future because it is a bit dangerous. PodTube class should only be called when
		// there is a valid user.
		$this->user = $user;
	}

	/**
	 * Public function exposing database inFeed function.
	 * @param $id
	 * @return bool
	 */
	public function isInFeed($id){
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
		for($x=0; $x<$this->user->getFeedLength() && isset($items[$x]); $x++){
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
		$feedDetails = $this->user->getFeedDetails();
		$imageURL = $feedDetails["icon"];
		$itunesAuthor = $feedDetails["itunesAuthor"];
		$feedTitle = $feedDetails["title"];
		$feedDescription = $feedDetails["description"];

		$feed = new RSS2;
		$feed->setTitle($feedTitle);
		$feed->setLink(LOCAL_URL);
		$feed->setDescription($feedDescription);
		$feed->setImage($feedTitle, LOCAL_URL, $imageURL);

		$feed->setDate(date(DATE_RSS, time()));
		$feed->setChannelElement('pubDate', date(\DATE_RSS, time()));
		$feed->setSelfLink(LOCAL_URL."user/".$this->user->getWebID()."/feed/");

		$feed->addNamespace("media", "http://search.yahoo.com/mrss/");
		$feed->addNamespace("itunes", "http://www.itunes.com/dtds/podcast-1.0.dtd");
		$feed->addNamespace("content", "http://purl.org/rss/1.0/modules/content/");
		$feed->addNamespace("sy", "http://purl.org/rss/1.0/modules/syndication/");

		$feed->setChannelElement('itunes:image', "", array('href'=>$imageURL));
		$feed->setChannelElement('itunes:author', $itunesAuthor);
		$feed->setChannelElement('itunes:category', "", array('text'=>'Technology'));
		$feed->setChannelElement('language', 'en-US');
		$feed->setChannelElement('itunes:explicit', "yes");
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

		$webPath = LOCAL_URL.DOWNLOAD_PATH."/".$id;
		$filePath = DOWNLOAD_PATH.DIRECTORY_SEPARATOR.$id;

		// Make description links clickable
		$descr = mb_ereg_replace('(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~\@!]*(\?\S+)?)?)?)', '<a href="\\1">\\1</a>', $descr);
		$descr = nl2br($descr);

		// Get the duration of the video and use it for the itunes:duration tag
		$duration = YouTube::getDuration($filePath.".mp3");

		$newItem->setTitle($title);
		$newItem->setLink("http://youtube.com/watch?v=$id");
		// Set description to be the title, author, thumbnail, and then the original video description
		$newItem->setDescription("<h1>$title</h1>
			<h2>$author</h2>
			<p><img class=\"alignleft size-medium\" src=\"$webPath.jpg\" alt=\"$title -- $author\" width=\"100%\" height=\"auto\"/></p>
			<p>$descr</p>");
		$newItem->addElement('media:content', array('media:title'=>$title), array('fileSize'=>filesize($filePath.".mp3"),
			'type'=> 'audio/mp3', 'medium'=>'audio', 'url'=>$webPath.'.mp3'));
		$newItem->addElement('media:thumbnail', null, array('url'=>$webPath.'.jpg'), false, true);
		$newItem->setEnclosure($webPath.".mp3", filesize($filePath.".mp3"), 'audio/mp3');
		$newItem->addElement('itunes:image', "", array('href'=>$webPath.'.jpg'));
		$newItem->addElement('itunes:author', $author);
		$newItem->addElement('itunes:duration', $duration);

		$newItem->setDate(date(DATE_RSS, $time));
		$newItem->setAuthor($author, 'me@me.com');
		// Set GUID, this is absolutely necessary
		$newItem->setId($webPath.".mp3", true);

		// Add the item generated to the global feed
		$feed->addItem($newItem);
		return $feed;
	}
}
