<?php

namespace AudioDidact\SupportedSites;

use AudioDidact\Video;

/**
 * Class YouTube
 */
class YouTube extends SupportedSite {
	// Setup global variables
	/** @var string YouTube URL */
	private $YouTubeBaseURL = "http://www.youtube.com/";

	/**
	 * YouTube constructor. Gets the video information, checks for it in the user's feed.
	 *
	 * @param string $str
	 * @param boolean $isVideo
	 * @throws \Exception
	 */
	public function __construct($str, $isVideo){
		$this->video = new Video();

		// If there is a URL/ID, continue
		if($str != null){
			$this->video->setURL($str);
			$this->video->setIsVideo($isVideo);

			// Set video ID from setYoutubeID and time to current time
			$this->video->setId($this->setYoutubeID($str));
			$this->video->setFilename($this->video->getId());
			$this->video->setThumbnailFilename($this->video->getFilename() . ".jpg");
			$this->video->setTime(time());

			$key = GOOGLE_API_KEY;

			// Get video author, title, and description from YouTube API
			$info = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet&id="
				. $this->video->getId() .
				"&fields=items/snippet/description,items/snippet/title,items/snippet/channelTitle&key=" .
				$key), true);
			// If the lookup fails, send this error to the UI as a JSON array
			if(!isset($info['items'][0]['snippet'])){
				throw new \Exception("ID Inaccessible");
			}
			$info = $info['items'][0]['snippet'];
			$this->video->setTitle($info["title"]);
			$this->video->setAuthor($info["channelTitle"]);
			$this->video->setDesc($info["description"]);
		}
	}

	/**
	 * Set YouTube ID from a given string using parseYoutubeURL
	 *
	 * @param string $str
	 * @return bool
	 * @throws \Exception
	 */
	private function setYoutubeID($str){
		// Try and parse the string into a usable ID
		$tmpId = $this->parseYoutubeURL($str);
		$vidId = ($tmpId !== false) ? $tmpId : $str;
		if(mb_strpos($vidId, "/playlist") > -1){
			throw new \Exception("URL is a playlist. AudioDidact does not currently support playlists.");
		}
		if(mb_strpos($vidId, "/c/") > -1 || strpos($vidId, "/channel/") > -1 || strpos($vidId, "/user/") > -1){
			throw new \Exception("URL is a channel. AudioDidact does not, and likely will not ever, support downloading of channels.");
		}
		$url = sprintf($this->YouTubeBaseURL . "watch?v=%s", $vidId);
		// Get HTTP status of the video url and make sure that it is
		// 200 = OK
		// 301 = Moved Permanently
		// 302 = Moved Temporarily
		if($this->cURLHTTPStatus($url) !== 200 && $this->cURLHTTPStatus($url) !== 301 && $this->cURLHTTPStatus($url)
			!== 302
		){
			throw new \Exception("Invalid Youtube video ID: $vidId");
		}

		return $vidId;
	}

	/**
	 * Download thumbnail using videoID from YouTube's image server
	 */
	public function downloadThumbnail(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$thumbnail = $path . $this->video->getThumbnailFilename();
		file_put_contents($thumbnail, fopen("https://i.ytimg.com/vi/" . $this->video->getID() . "/mqdefault.jpg", "r"));
		// Set the thumbnail file as publicly accessible
		@chmod($thumbnail, 0775);
	}

	/**
	 * Download video using download URL from Python script and then call downloadWithPercentage to actually download
	 * the video
	 */
	public function downloadVideo(){
		$path = getcwd() . DIRECTORY_SEPARATOR . DOWNLOAD_PATH . DIRECTORY_SEPARATOR;
		$videoFilename = $this->video->getFilename() . ".mp4";
		$videoPath = $path . $videoFilename;

		$url = $this->getDownloadURL($this->video->getID());
		if(mb_strpos($url, "Error:") > -1){
			error_log("$url");
			throw new \Exception($url);
		}
		try{
			/* Actually download the video from the url and print the
			 * percentage to the screen with JSON
			 */
			$this->downloadWithPercentage($url, $videoPath);
			// Set the video file as publicly accessible
			@chmod($videoPath, 0775);
			$this->video->setDuration(SupportedSite::getDurationSeconds($videoPath));
		}
		catch(\Exception $e){
			throw $e;
		}
	}

	/**
	 * Gets lowest quality mp4 download url based on a given id.
	 *
	 * @param $id
	 * @return string
	 * @throws \Exception
	 */
	private function getDownloadURL($id){
		$url = $this->YouTubeBaseURL . "watch?v=" . $id;
		$html = file_get_contents($url);
		$restrictionPattern = "og:restrictions:age";

		if(mb_strpos($html, $restrictionPattern) > -1){
			return "Error: Age restricted video. Unable to download.";
		}
		$jsonStartPattern = "ytplayer.config = ";
		$patternIndex = mb_strpos($html, $jsonStartPattern);
		// In case video is unable to play
		if($patternIndex == -1){
			return "Error: Unable to find start pattern.";
		}

		$start = $patternIndex + mb_strlen($jsonStartPattern);
		$html = mb_substr($html, $start);

		$unmatchedBracketsCount = 0;
		$index = 1;
		$htmlArr = mb_str_split($html);
		$i = 0;
		foreach($htmlArr as $i => $ch){
			if($ch == "{"){
				$unmatchedBracketsCount += 1;
			}
			else if($ch == "}"){
				$unmatchedBracketsCount -= 1;
				if($unmatchedBracketsCount == 0){
					break;
				}
			}
		}
		$offset = $index + $i;

		$youtubeJSONData = json_decode(mb_substr($html, 0, $offset), true);
		if($youtubeJSONData == null){
			throw new \Exception("<h3>Download Failed</h3><h4>Unable to find JSON data from YouTube</h4>");
		}

		if(isset($youtubeJSONData["args"]["livestream"]) && $youtubeJSONData["args"]["livestream"] && (!isset($youtubeJSONData["args"]["url_encoded_fmt_stream_map"]) || $youtubeJSONData["args"]["url_encoded_fmt_stream_map"] == "")){
			throw new \Exception("<h3>Download Failed</h3><h4>This URL is a livestream, try again when the stream has ended</h4>");
		}

		if(!isset($youtubeJSONData["args"]["url_encoded_fmt_stream_map"]) || $youtubeJSONData["args"]["url_encoded_fmt_stream_map"] == ""){
			error_log("Couldn't download ".$id." because could not find url_encoded_fmt_stream_map");
			error_log(json_encode($youtubeJSONData));
			throw new \Exception("<h3>Download Failed</h3><h4>Try again later</h4>");
		}
		$encodedStreamMap = $youtubeJSONData["args"]["url_encoded_fmt_stream_map"];
		$dct = [];
		$videos = explode(",", $encodedStreamMap);
		foreach($videos as $i => $video){
			$video = explode("&", $video);
			foreach($video as $v){
				$key = explode("=", $v)[0];
				$value = explode("=", $v)[1];
				$dct[$key][] = urldecode($value);
			}
		}
		$youtubeJSONData["args"]["stream_map"] = $dct;
		$streamMap = $dct;
		unset($dct, $videos, $html, $htmlArr, $youtubeJSONData);

		$videoURLs = $streamMap["url"];
		$downloads = [];
		foreach($videoURLs as $i => $vurl){
			$qualityProfile = $this->getQualityProfilesFromURL($vurl);
			$downloads[] = ["url" => $vurl, "ext" => $qualityProfile["extension"], "res" => $qualityProfile["resolution"]];
		}
		$downloadURL = "";
		$resolution = 999999;
		// Find lowest quality mp4
		foreach($downloads as $v){
			if($v["ext"] == "mp4" && intval(mb_substr($v["res"], 0, -1)) < $resolution){
				$resolution = intval(mb_substr($v["res"], 0, -1));
				$downloadURL = $v["url"];
			}
		}

		return $downloadURL;
	}

	/**
	 * Returns a quality profile or false based on a url.
	 *
	 * @param $url
	 * @return bool|mixed
	 */
	private function getQualityProfilesFromURL($url){
		$qp = [];
		$qp[5] = ["flv", "240p", "Sorenson H.263", "N/A", "0.25", "MP3", "64"];
		$qp[17] = ["3gp", "144p", "MPEG-4 Visual", "Simple", "0.05", "AAC", "24"];
		$qp[36] = ["3gp", "240p", "MPEG-4 Visual", "Simple", "0.17", "AAC", "38"];
		$qp[43] = ["webm", "360p", "VP8", "N/A", "0.5", "Vorbis", "128"];
		$qp[100] = ["webm", "360p", "VP8", "3D", "N/A", "Vorbis", "128"];
		$qp[18] = ["mp4", "360p", "H.264", "Baseline", "0.5", "AAC", "96"];
		$qp[22] = ["mp4", "720p", "H.264", "High", "2-2.9", "AAC", "192"];
		$qp[82] = ["mp4", "360p", "H.264", "3D", "0.5", "AAC", "96"];
		$qp[83] = ["mp4", "240p", "H.264", "3D", "0.5", "AAC", "96"];
		$qp[84] = ["mp4", "720p", "H.264", "3D", "2-2.9", "AAC", "152"];
		$qp[85] = ["mp4", "1080p", "H.264", "3D", "2-2.9", "AAC", "152"];
		foreach($qp as $k => $q){
			$keys = ["extension", "resolution", "video_codec", "profile", "video_bitrate", "audio_codec", "audio_bitrate"];
			foreach($keys as $k2 => $v){
				$qp[$k][$v] = $q[$k2];
			}
			foreach(array_keys($qp[$k]) as $key){
				if(!in_array($key, $keys, true)){
					unset($qp[$k][$key]);
				}
			}
		}

		$itagPattern = '/itag=(\d+)/';
		preg_match_all($itagPattern, $url, $itag);
		if(isset($itag[1][0]) && intval($itag[1][0]) > -1){
			$itag = intval($itag[1][0]);

			return $qp[$itag];
		}

		return false;
	}

	/**
	 * Use cURL to get the HTTP status of a given URL
	 *
	 * @param $url
	 * @return int
	 */
	private function cURLHTTPStatus($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko Firefox/11.0");
		curl_setopt($ch, CURLOPT_REFERER, $this->YouTubeBaseURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_exec($ch);
		$int = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return intval($int);
	}

	/**
	 * Parse a YouTube URL to get the video ID
	 *
	 * @param $url
	 * @return bool
	 */
	private function parseYoutubeURL($url){
		$pattern = '#^(?:https?://)?';
		$pattern .= '(?:www\.)?';
		$pattern .= '(?:';
		$pattern .= 'youtu\.be/';
		$pattern .= '|youtube\.com';
		$pattern .= '(?:';
		$pattern .= '/embed/';
		$pattern .= '|/v/';
		$pattern .= '|/watch\?v=';
		$pattern .= '|/watch\?.+&v=';
		$pattern .= ')';
		$pattern .= ')';
		$pattern .= '([\w-]{11})';
		$pattern .= '(?:.+)?$#x';
		preg_match($pattern, $url, $matches);

		return (isset($matches[1])) ? $matches[1] : false;
	}
}
