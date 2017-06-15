<?php

/**
 * Returns Pug rendered HTML for the User page, either view or edit
 *
 * @param $webID string webID of the user's page to be rendered
 * @param $edit boolean true if the user is logged in and viewing their own page
 * @param null|string $verifyEmail null or string if the user is trying to verify their email address
 * @return string HTML of User's page from Pug
 */
function makeUserPage($webID, $edit, $verifyEmail = null){
	$dal = getDAL();
	$user = $dal->getUserByWebID($webID);
	if($user == null){
		echo "<script>alert(\"Invalid User!\");window.location = \"/" . SUBDIR . "\";</script>";
		exit();
	}
	if($edit){
		$title = "User Page | $webID | Edit";
	}
	else{
		$title = "User Page | $webID";
	}
	$emailVerify = 0;
	if($verifyEmail != null && !$user->isEmailVerified()){
		$result = $user->verifyEmailVerificationCode($verifyEmail);
		// If the email verification code is correct, update the user information
		if($result){
			$user->setEmailVerified(1);
			$user->setEmailVerificationCodes([]);
			$dal->updateUser($user);
			$emailVerify = 1;
		}
		else{
			$emailVerify = 2;
		}
	}
	$userData = ["privateFeed" => $user->isPrivateFeed(), "fName" => $user->getFname(), "lName" => $user->getLname(),
		"gender" => $user->getGender(), "webID" => $user->getWebID(), "username" => $user->getUsername(),
		"email" => $user->getEmail(), "feedLength" => $user->getFeedLength(), "feedDetails" => $user->getFeedDetails()
	];
	$episodeData = [];
	if($edit || $userData["privateFeed"] == 0){
		$items = $dal->getFeed($user);
		for($x = 0; $x < $user->getFeedLength() && isset($items[$x]); $x++){
			/** @var \AudioDidact\Video $i */
			$i = $items[$x];
			$descr = $i->getDesc();

			// Limit description to 3 lines initially
			$words = explode("\n", $descr, 4);
			if(count($words) > 3){
				$words[3] = "<p id='" . $i->getId() . "' style='display:none;'>" . trim($words[3]) . " </p></p>";
				$words[4] = "<a onclick='$(\"#" . $i->getId() . "\").show();'>Continue Reading...</a>";
			}
			$descr = implode("\n", $words);
			$descr = mb_ereg_replace('(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~\@!]*(\?\S+)?)?)?)', '<a href="\\1" target="_blank">\\1</a>', $descr);
			$descr = nl2br($descr);

			$thumb = LOCAL_URL . DOWNLOAD_PATH . '/' . $i->getThumbnailFilename();
			$episodeFile = LOCAL_URL . DOWNLOAD_PATH . '/' . $i->getFilename() . $i->getFileExtension();

			$episodeData[] = ["title" => $i->getTitle(), "author" => $i->getAuthor(), "id" => $i->getId(),
				"description" => $descr, "thumbnail" => $thumb, "episodeFile" => $episodeFile, "isVideo" => $i->isIsVideo()];
		}
	}

	$options = ["edit" => $edit, "episodes" => $episodeData, "emailverify" => $emailVerify, "user" => $userData,
		"stats" => generateStatistics($user)];

	return generatePug("views/userPage.pug", $title, $options);
}

/**
 * Returns Array with informative statistics about all videos in the feed
 *
 * @param \AudioDidact\User $user
 * @return array
 */
function generateStatistics(\AudioDidact\User $user){
	$dal = getDAL();
	$stats = [];
	$feed = $dal->getFullFeedHistory($user);
	$stats["numVids"] = count($feed);
	$time = 0;
	foreach($feed as $v){
		/** @var \AudioDidact\Video $v */
		$time += $v->getDuration();
	}

	$timeConversion = secondsToTime($time);
	$timeList = [];
	if($timeConversion["d"] > 0){
		$timeList[] = $timeConversion["d"] . " " . pluralize("day", $timeConversion["d"]);
	}
	if($timeConversion["h"] > 0){
		$timeList[] = $timeConversion["h"] . " " . pluralize("hour", $timeConversion["h"]);
	}
	if($timeConversion["m"] > 0){
		$timeList[] = $timeConversion["m"] . " " . pluralize("minute", $timeConversion["m"]);
	}
	if($timeConversion["s"] >= 0){
		$timeList[] = $timeConversion["s"] . " " . pluralize("second", $timeConversion["s"]);
	}
	$stats["totalTime"] = stringListicle($timeList);

	return $stats;
}

/**
 * Convert number of seconds into hours, minutes and seconds
 * and return an array containing those values
 *
 * @param integer $inputSeconds Number of seconds to parse
 * @return array
 */

function secondsToTime($inputSeconds){
	$secondsInAMinute = 60;
	$secondsInAnHour = 60 * $secondsInAMinute;
	$secondsInADay = 24 * $secondsInAnHour;

	// extract days
	$days = floor($inputSeconds / $secondsInADay);

	// extract hours
	$hourSeconds = $inputSeconds % $secondsInADay;
	$hours = floor($hourSeconds / $secondsInAnHour);

	// extract minutes
	$minuteSeconds = $hourSeconds % $secondsInAnHour;
	$minutes = floor($minuteSeconds / $secondsInAMinute);

	// extract the remaining seconds
	$remainingSeconds = $minuteSeconds % $secondsInAMinute;
	$seconds = ceil($remainingSeconds);

	// return the final array
	return [
		'd' => (int)$days,
		'h' => (int)$hours,
		'm' => (int)$minutes,
		's' => (int)$seconds,
	];
}
