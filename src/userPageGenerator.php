<?php

use AudioDidact\GlobalFunctions;

/**
 * Returns Pug rendered HTML for the User page, either view or edit
 *
 * @param $webID string webID of the user's page to be rendered
 * @param $edit boolean true if the user is logged in and viewing their own page
 * @param null|string $verifyEmail null or string if the user is trying to verify their email address
 * @return string HTML of User's page from Pug
 */
function makeUserPage($webID, $edit, $verifyEmail = null){
	$dal = GlobalFunctions::getDAL();
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

	$options = ["edit" => $edit, "episodes" => $episodeData, "emailverify" => $emailVerify, "pageUser" => $userData,
		"stats" => generateStatistics($user)];

	return GlobalFunctions::generatePug("views/userPage.pug", $title, $options);
}

/**
 * Returns Array with informative statistics about all videos in the feed
 *
 * @param \AudioDidact\User $user
 * @return array
 */
function generateStatistics(\AudioDidact\User $user){
	$dal = GlobalFunctions::getDAL();
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
	foreach($timeConversion as $unit => $value){
		if($value > 0){
			$timeList[] = $value . " " . GlobalFunctions::pluralize($unit, $value);
		}
	}
	$stats["totalTime"] = GlobalFunctions::stringListicle($timeList);

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
	$conversion = ["second" => ["second" => 1],
		"minute" => ["second" => 60],
		"hour" => ["minute" => 60],
		"day" => ["hour" => 24],
		"week" => ["day" => 7],
		"month" => ["week" => 4],
		"year" => ["day" => 365]];

	$baseUnit = "";
	$newConversion = [];
	foreach($conversion as $unit => $convertArr){
		if(array_key_exists($unit, $convertArr) && $convertArr[$unit] == 1){
			$baseUnit = $unit;
		}
		foreach($convertArr as $conversionUnit => $conversionFactor){
			$conversion[$unit][$baseUnit] = $conversion[$conversionUnit][$baseUnit] * $conversionFactor;
			$newConversion[$unit] = $conversion[$conversionUnit][$baseUnit] * $conversionFactor;
		}
	}

	// Reverse sort so that the largest units are iterated through first
	arsort($newConversion);

	$remainingSeconds = $inputSeconds;
	$outputArray = [];
	foreach($newConversion as $unit => $conversionFactor){
		$val = intval(floor($remainingSeconds / $conversionFactor));
		if($val > 0){
			$outputArray[$unit] = $val;
		}
		$remainingSeconds = $remainingSeconds % $conversionFactor;
	}

	return $outputArray;
}
