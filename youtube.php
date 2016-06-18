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

class youtube{
	// Setup global variables
	private $csvFilePath = 'feed.csv';
	private $downloadPath = 'temp';
	private $localUrl = "http://example.com/"; // Change to your hostname
	private $googleAPIServerKey = "********"; // Add server key here
	private $rssFilePath = "rss.xml";

	// Setup video variables
	private $descr = "";
	private $videoID = "";
	private $videoTitle = "";
	private $videoAuthor = "";
	private $time;
	
	public function __construct($str=NULL, $instant=FALSE){		
		$this->YT_BASE_URL = "http://www.youtube.com/";
		
		// Make download folder if it does not exist
		if(!file_exists($this->downloadPath)){
			mkdir($this->downloadPath);
		}
		
		// If there is a URL/ID, continue
		if($str != NULL){
			// Set video ID from setYoutubeID and time to current time
			$this->videoID = $this->setYoutubeID($str);
			$this->time = time();
			// Check if the video already exists in the CSV. If it does, then we do not need to get the information from the YouTube API again
			if(!$this->isInCSV()){
				// Get video author, title, and description from YouTube API
				$info = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet&id=".$this->videoID."&fields=items/snippet/description,items/snippet/title,items/snippet/channelTitle&key=".$this->googleAPIServerKey), true);
				// If the lookup fails, send this error to the UI as a JSON array
				if(!isset($info['items'][0]['snippet'])){
					echo json_encode(['stage'=>-1, 'error'=>"ID Inaccessible", 'progress'=>0]);
					throw new RuntimeException();
				}
				$info = $info['items'][0]['snippet'];
				$this->videoTitle = $info["title"];
				$this->videoAuthor = $info["channelTitle"];
				$this->descr = $info["description"];
			}
			
			// If we are supposed to download the video immediately, then get the thumbnail, video, and mp3
			if($instant === true){
				$this->downloadThumbnail();
				$this->downloadVideo();
				$this->convert();
			}
		}
	}
	
	// Accessor methods
	public function getVideoID(){
		return $this->videoID;
	}

	public function getDownloadPath(){
		return $this->downloadPath;
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

	public function getRssPath(){
		return $this->rssFilePath;
	}
	
	// Adds the current video to the CSV file
	public function addToCSV(){
		$list = array($this->videoID, utf8_encode($this->videoTitle), utf8_encode($this->videoAuthor), $this->time, utf8_encode($this->descr));
		$handle = fopen($this->csvFilePath, "a");
		fputcsv($handle, [json_encode($list)]);
		fclose($handle);
	}
	
	// Checks if the current video is in the CSV file
	public function isInCSV(){
		if(file_exists($this->csvFilePath)){
			$csv = array_map('str_getcsv', file($this->csvFilePath));
			$csv = array_reverse($csv);
			foreach($csv as $k=>$v){
				$v = json_decode($v[0], true);
				// If the video ID is in the CSV, then set all the other local settings from the CSV and return true
				if($v[0] == $this->videoID){
					$this->videoAuthor = utf8_decode($v[2]);
					$this->videoTitle = utf8_decode($v[1]);
					$this->videoID = $v[0];
					$this->time = $v[3];
					$this->descr = utf8_decode($v[4]);
					return true;
				}
			}
		}
		return false;
	}
	
	// Checks if all thumbnail, video, and mp3 are downloaded and have a length (ie. video or audio are not null)
	public function allDownloaded(){
		$downloadFilePath = $this->downloadPath.DIRECTORY_SEPARATOR.$this->videoID;
		// If the thumbnail has not been downloaded, go ahead and download it
		if(!file_exists($downloadFilePath.".jpg")){
			$this->downloadThumbnail();
		}
		// If the mp3 and mp4 files exist, check if the mp3 has a duration that is not null
		if(file_exists($downloadFilePath.".mp3") && file_exists($downloadFilePath.".mp4")){
			if($this->getDuration($downloadFilePath.".mp3")){
				return true;
			}
		}
		// If only the mp4 is downloaded (and has a duration) or the mp3 duration is null, then convert the mp4 to mp3
		if(file_exists($downloadFilePath.".mp4") && $this->getDuration($downloadFilePath.".mp4")){
			$this->convert();
			return true;
		}
		// If all else fails, return false
		return false;
	}
	
	// Converts mp4 video to mp3 audio
	public function convert(){
		$path = getcwd().DIRECTORY_SEPARATOR.$this->downloadPath.DIRECTORY_SEPARATOR;
		$ffmpeg_infile = $path . $this->videoID .".mp4";
		$ffmpeg_outfile = $path . $this->videoID .".mp3";
		
		// Use ffmpeg to convert the audio in the background and save output to a file called videoID.txt
		$cmd = "ffmpeg -i \"$ffmpeg_infile\" -y -q:a 0 -map a \"$ffmpeg_outfile\" 1> ".$this->videoID.".txt 2>&1";
		// Start the command in the background
		pclose(popen("start /B ".$cmd, "r"));
		$progress = 0;
		// Get the conversion progress and output the progress to the UI using a JSON array
		while($progress != 100){
			$content = @file_get_contents($this->videoID.'.txt');
			// Get the total duration of the file
			preg_match("/Duration: (.*?), start:/", $content, $matches);
			// If there is no match, then wait and continue
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

			// Matches time of the converted file and gets the percentage complete
			$rawTime = array_pop($matches);
			if (is_array($rawTime)){$rawTime = array_pop($rawTime);}
			$ar = array_reverse(explode(":", $rawTime));
			$time = floatval($ar[0]);
			if (!empty($ar[1])) $time += intval($ar[1]) * 60;
			if (!empty($ar[2])) $time += intval($ar[2]) * 60 * 60;
			$progress = round(($time/$duration) * 100);

			// Send progress to UI
			$response = array('stage' =>1, 'progress' => $progress);
			echo json_encode($response);
			usleep(500000);
		}
		// Delete the temporary file that contained the ffmpeg output
		@unlink($this->videoID.".txt");
		clearstatcache();
		return;
	}

	// Download video using download URL from Python script and then call downloadWithPercentage to actually download the video
	public function downloadVideo(){
		$id = $this->videoID;
		$path = getcwd().DIRECTORY_SEPARATOR.$this->downloadPath.DIRECTORY_SEPARATOR;
		$videoFilename = "$id.mp4";
		$video = $path . $videoFilename;
		
		$url = exec("python getYTDownloadURL.py $id");
		$this->downloadWithPercentage($url, $video);
		@chmod($video, 0775); // Set the video file as publicly accessible
		
		return;
	}
	
	// Download thumbnail using videoID from YouTube's image server
	public function downloadThumbnail(){
		$thumbFilename = $this->videoID.".jpg";
		$path = getcwd().DIRECTORY_SEPARATOR.$this->downloadPath.DIRECTORY_SEPARATOR;
		$thumbnail = $path . $thumbFilename;
		file_put_contents($thumbnail, fopen("http://img.youtube.com/vi/".$this->videoID."/mqdefault.jpg", "r"));
		@chmod($thumbnail, 0775); // Set the thumbnail file as publicly accessible
	}
	
	// Download the video to $localFile with a given $url
	// While downloading output progress to UI as JSON array
	private function downloadWithPercentage($url, $localFile){
		// Use CURL to get the download content length in order to print the progress
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);
		if ($data === false) {
			$response = array('stage' =>-1, 'progress' => 0, 'error'=> "Download failed, URL tried was ".$url);
			echo json_encode($response);
			throw new RuntimeException();
		}
		
		// Get content length in bytes
		$contentLength = 'unknown';
		if (preg_match_all('/Content-Length: (\d+)/', $data, $matches)) {
			$contentLength = (int)$matches[count($matches)-1][count($matches[count($matches)-1])-1];
		}
		
		if(intval($contentLength)>0){
			// Open local and remote files for write and read respectively
			$remote = fopen($url, 'r');
			$local = fopen($localFile, 'w');

			$read_bytes = 0;
			// Read until the end of the remote file
			while(!feof($remote)) {
				// Read 4KB and write them to the local file
				$buffer = fread($remote, 4096);
				fwrite($local, $buffer);
				$read_bytes += 4096;
				
				// Get progress percentage from the read bytes and total length
				$progress = min(100, 100 * $read_bytes / $contentLength);
				// Print progress to the UI using a JSON array
				$response = array('stage' =>0, 'progress' => $progress);
				echo json_encode($response);
			}
			// Close the handles of both files
			fclose($remote);
			fclose($local);
		}
		
		return true;
	}
	
	// Use cURL to get the HTTP status of a given URL
	private function curl_httpstatus($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko Firefox/11.0");
		curl_setopt($ch, CURLOPT_REFERER, $this->YT_BASE_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_exec($ch);
		$int = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return intval($int);
	}
	
	// Set YouTube ID from a given string using parse_yturl
	private function setYoutubeID($str){
		$tmp_id = self::parse_yturl($str);  // Try and parse the string into a usable ID
		$vid_id = ($tmp_id !== FALSE) ? $tmp_id : $str;
		$url = sprintf($this->YT_BASE_URL . "watch?v=%s", $vid_id);
		// Get HTTP status of the video url and make sure that it is 
		// 200 = OK
		// 301 = Moved Permanently
		// 302 = Moved Temporarily
		if(self::curl_httpstatus($url) !== 200 && self::curl_httpstatus($url) !== 301 && self::curl_httpstatus($url)
			!== 302){
			throw new Exception("Invalid Youtube video ID: $vid_id");
		}
		return $vid_id;
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

	// Delete any videos in the CSV over 50 videos in reverse-chronological order
	public function deleteLast($file){
		$downloadPath = $this->downloadPath;
		if(file_exists($file)){
			$csv = array_map('str_getcsv', file($file));
			$csv = array_reverse($csv, false);
			foreach($csv as $k=>$v){
				$v = json_decode($v[0], true);
				$author = utf8_decode($v[2]);
				$title = utf8_decode($v[1]);
				$id = $v[0];
				$time = $v[3];
				$descr = utf8_decode($v[4]);
				if($k >= 50){
					@unlink($downloadPath.DIRECTORY_SEPARATOR.$id.".mp3");
					@unlink($downloadPath.DIRECTORY_SEPARATOR.$id.".mp4");
					@unlink($downloadPath.DIRECTORY_SEPARATOR.$id.".jpg");
					continue;
				}
				$handle = fopen($file.".tmp", "a");
				fputcsv($handle, [json_encode([$id, utf8_encode($title), utf8_encode($author), $time, utf8_encode($descr)])]);
				fclose($handle);
			}
			@unlink($file); // Delete the existing CSV
			@rename($file.".tmp", $file); // Rename the temporary CSV to the same name as the real CSV
		}
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
		$duration = $this->getDuration($this->downloadPath.DIRECTORY_SEPARATOR.$id.".mp3");
		
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
		$myFeed = $feed->generateFeed(); // Make the feed
		return $feed;
	}
	
	// Get duration of media file from ffmpeg
	private function getDuration($file){
		$dur = shell_exec("ffmpeg.exe -i ".$file." 2>&1");
		if(preg_match("/: Invalid /", $dur)){
			return false;
		}
		preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $dur, $duration);
		if(!isset($duration[1])){
			return false;
		}
		$duration = $duration[1].":".$duration[2].":".$duration[3];
		return $duration;
	}
	
	// Parse a YouTube URL to get the video ID
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