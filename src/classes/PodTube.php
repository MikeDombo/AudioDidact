<?php

namespace AudioDidact;
// Include RSS feed generation library and other classes that are used.
require_once __DIR__ . "/../header.php";
use \FeedWriter\RSS2;

/**
 * Class PodTube
 */
class PodTube {
	/**
	 * Make the RSS feed from the database
	 *
	 * @param \AudioDidact\User $user
	 * @param \AudioDidact\DAL $dal
	 * @param bool $emptyFeed true if the generated feed should be empty and not saved to the db directly
	 * @return \FeedWriter\RSS2|mixed
	 */
	public static function makeFullFeed(User $user, DAL $dal, $emptyFeed = false){
		// Setup global feed values
		$fe = self::makeFeed($user);
		if(!$emptyFeed){
			$items = $dal->getFeed($user);
			foreach($items as $video){
				/** @var Video $video */
				$fe = self::addFeedItem($fe, $video);
			}

			// Save the generated feed to the db
			$dal->setFeedText($user, $fe->generateFeed());
		}

		return $fe;
	}

	/**
	 * Generate the global feed header variables
	 *
	 * @param \AudioDidact\User $user
	 * @return \FeedWriter\RSS2
	 */
	private static function makeFeed(User $user){
		$feedDetails = $user->getFeedDetails();
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
		$feed->setChannelElement('pubDate', date(DATE_RSS, time()));
		$feed->setSelfLink(LOCAL_URL . "user/" . $user->getWebID() . "/feed/");

		$feed->addNamespace("media", "http://search.yahoo.com/mrss/");
		$feed->addNamespace("itunes", "http://www.itunes.com/dtds/podcast-1.0.dtd");
		$feed->addNamespace("content", "http://purl.org/rss/1.0/modules/content/");
		$feed->addNamespace("sy", "http://purl.org/rss/1.0/modules/syndication/");

		$feed->setChannelElement('itunes:image', "", ['href' => $imageURL]);
		$feed->setChannelElement('itunes:author', $itunesAuthor);
		$feed->setChannelElement('itunes:category', "", ['text' => 'Technology']);
		$feed->setChannelElement('language', 'en-US');
		$feed->setChannelElement('itunes:explicit', "yes");
		$feed->setChannelElement('sy:updatePeriod', "hourly");
		$feed->setChannelElement('sy:updateFrequency', "1");
		$feed->setChannelElement('ttl', "15");

		return $feed;
	}

	/**
	 * Add an item to the RSS feed
	 *
	 * @param $feed
	 * @param $video Video
	 * @return mixed
	 */
	private function addFeedItem($feed, $video){
		$title = $video->getTitle();
		$author = $video->getAuthor();
		$descr = $video->getDesc();

		/** @var \FeedWriter\Feed $feed */
		$newItem = $feed->createNewItem();

		$filenameWithExt = $video->getFilename() . $video->getFileExtension();
		$webPath = LOCAL_URL . DOWNLOAD_PATH . "/" . $filenameWithExt;
		$webThumb = LOCAL_URL . DOWNLOAD_PATH . "/" . $video->getThumbnailFilename();
		$filePath = DOWNLOAD_PATH . DIRECTORY_SEPARATOR . $filenameWithExt;

		// Make description links clickable
		$descr = mb_ereg_replace('(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~\@!]*(\?\S+)?)?)?)', '<a href="\\1">\\1</a>', $descr);
		$descr = nl2br($descr);

		// Get the duration of the video and use it for the itunes:duration tag
		$duration = SupportedSites\SupportedSite::getDuration($filePath);

		$newItem->setTitle($title);
		$newItem->setLink($video->getURL());
		// Set description to be the title, author, thumbnail, and then the original video description
		$newItem->setDescription("<h1>$title</h1><h2>$author</h2>
			<p><img class=\"alignleft size-medium\" src=\"$webThumb\" alt=\"" . htmlentities($title) . " -- " . htmlentities($author) . "
			\"width=\"100%\" height=\"auto\"/></p><p>$descr</p>");

		if($video->isIsVideo()){
			$newItem->addElement('media:content', ['media:title' => $title], ['fileSize' => filesize($filePath),
				'type' => 'video/mp4', 'medium' => 'video', 'url' => $webPath]);
			$newItem->setEnclosure($webPath, filesize($filePath), 'video/mp4');
		}
		else{
			$newItem->addElement('media:content', ['media:title' => $title], ['fileSize' => filesize($filePath),
				'type' => 'audio/mpeg', 'medium' => 'audio', 'url' => $webPath]);
			$newItem->setEnclosure($webPath, filesize($filePath), 'audio/mpeg');
		}

		$newItem->addElement('media:thumbnail', null, ['url' => $webThumb], false, true);
		$newItem->addElement('itunes:image', "", ['href' => $webThumb]);
		$newItem->addElement('itunes:author', $author);
		$newItem->addElement('itunes:duration', $duration);

		$newItem->setDate(date(DATE_RSS, $video->getTime()));
		$newItem->setAuthor($author, 'me@me.com');
		// Set GUID, this is absolutely necessary
		$newItem->setId("audiodidact/" . $filenameWithExt, false);

		// Add the item generated to the global feed
		$feed->addItem($newItem);

		return $feed;
	}
}
