<?php
function getDownloadURL($id){
	$url = "http://youtube.com/watch?v=".$id;
	$title = "";
	$html = file_get_contents($url);
	$restriction_pattern = "og:restrictions:age";

	if(strpos($html, $restriction_pattern)>-1){
		return "Error: Age restricted video. Unable to download";
	}
	$json_object = "";
	$json_start_pattern = "ytplayer.config = ";
	$pattern_idx = strpos($html, $json_start_pattern);
	# In case video is unable to play
	if($pattern_idx == -1){
		return "Error: Unable to find start pattern.";
	}

	$start = $pattern_idx + strlen($json_start_pattern);
	$html = substr($html, $start);

	$unmatched_brackets_num = 0;
	$index = 1;
	$htmlArr = str_split($html);
	foreach($htmlArr as $i=>$ch){
		if($ch == "{"){
			$unmatched_brackets_num += 1;
		}
		else if($ch == "}"){
			$unmatched_brackets_num -= 1;
			if($unmatched_brackets_num == 0){
				break;
			}
		}
	}
	$offset = $index + $i;

	$json_object = json_decode(substr($html, 0, $offset), true);
	$encoded_stream_map = $json_object["args"]["url_encoded_fmt_stream_map"];

	$dct = array();
	$videos = explode(",", $encoded_stream_map);

	# Unquote the characters and split to parameters.
	foreach($videos as $i=>$video){
		$video = explode("&", $video);
		foreach($video as $v){
			$key = explode("=", $v)[0];
			$value = explode("=", $v)[1];
			$dct[$key][] = urldecode($value);
		}
	}
	$json_object["args"]["stream_map"] = $dct;
	$stream_map = $dct;
	unset($dct, $videos, $html, $htmlArr);

	$video_data = $json_object;
	unset($json_object);


	$js_url = "http:" + $video_data["assets"]["js"];
	$video_urls = $stream_map["url"];
	$downloads = array();
	foreach($video_urls as $i=>$vurl){
		$quality_profile = get_quality_profile_from_url($vurl);
		$downloads[] = ["url"=>$vurl, "ext"=>$quality_profile["extension"], "res"=>$quality_profile["resolution"]];
	}
	$downloadURL = "";
	$resolution = 999999;
	foreach($downloads as $v){
		if($v["ext"] == "mp4" && intval(substr($v["res"], 0, -1)) < $resolution){
			$resolution = intval(substr($v["res"], 0, -1));
			$downloadURL = $v["url"];
		}
	}

	return $downloadURL;
}

function get_quality_profile_from_url($url){
	$QUALITY_PROFILES = [];
	$QUALITY_PROFILES[5] = ["flv", "240p", "Sorenson H.263", "N/A", "0.25", "MP3", "64"];
	$QUALITY_PROFILES[17] = ["3gp", "144p", "MPEG-4 Visual", "Simple", "0.05", "AAC", "24"];
	$QUALITY_PROFILES[36] = ["3gp", "240p", "MPEG-4 Visual", "Simple", "0.17", "AAC", "38"];
	$QUALITY_PROFILES[43] = ["webm", "360p", "VP8", "N/A", "0.5", "Vorbis", "128"];
	$QUALITY_PROFILES[100] = ["webm", "360p", "VP8", "3D", "N/A", "Vorbis", "128"];
	$QUALITY_PROFILES[18] = ["mp4", "360p", "H.264", "Baseline", "0.5", "AAC", "96"];
	$QUALITY_PROFILES[22] = ["mp4", "720p", "H.264", "High", "2-2.9", "AAC", "192"];
	$QUALITY_PROFILES[82] = ["mp4", "360p", "H.264", "3D", "0.5", "AAC", "96"];
	$QUALITY_PROFILES[83] = ["mp4", "240p", "H.264", "3D", "0.5", "AAC", "96"];
	$QUALITY_PROFILES[84] = ["mp4", "720p", "H.264", "3D", "2-2.9", "AAC", "152"];
	$QUALITY_PROFILES[85] = ["mp4", "1080p", "H.264", "3D", "2-2.9", "AAC", "152"];
	foreach($QUALITY_PROFILES as $k=>$q){
		$keys = ["extension","resolution","video_codec","profile","video_bitrate","audio_codec","audio_bitrate"];
		foreach($keys as $k2=>$v){
			$QUALITY_PROFILES[$k][$v] = $q[$k2];
		}
		foreach($QUALITY_PROFILES[$k] as $key=>$value){
			if(!in_array($key, $keys, true)){
				unset($QUALITY_PROFILES[$k][$key]);
			}
		}
	}
	
	$reg_exp = '/itag=(\d+)/';
	preg_match_all($reg_exp, $url, $itag);
	if(isset($itag[1][0]) && intval($itag[1][0]) > -1){
		$itag = intval($itag[1][0]);
		$quality_profile = $QUALITY_PROFILES[$itag];
		return $quality_profile;
	}
	return false;
}
?>