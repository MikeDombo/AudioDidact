<?php
function makeUserPage($webID, $edit, $loggedIn, $verifyEmail = null){
	$myDalClass = ChosenDAL;
	/** @var $dal \DAL */
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
	$user = $dal->getUserByWebID($webID);
	if($user == null){
		echo "<script>alert(\"Invalid User!\");window.location = \"/".SUBDIR."\";</script>";
		exit();
	}
	if($edit){
		$title = "User Page | $webID | Edit";
	}else{
		$title = "User Page | $webID";
	}
	$emailVerify = 0;
	if($verifyEmail != null && !$user->isEmailVerified()){
		$result = $user->verifyEmailVerificationCode($verifyEmail);
		if($result){
			$user->setEmailVerified(1);
			$user->setEmailVerificationCodes([]);
			$dal->updateUser($user);
			$emailVerify = 1;
		}else{
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
			/** @var Video $i */
			$i = $items[$x];
			$descr = $i->getDesc();

			// Limit description to 3 lines initially
			$words = explode("\n", $descr, 4);
			if(count($words) > 3){
				$words[3] = "<p id='".$i->getId()."' style='display:none;'>".trim($words[3])." </p></p>";
				$words[4] = "<a onclick='$(\"#".$i->getId()."\").show();'>Continue Reading...</a>";
			}
			$descr = implode("\n", $words);
			$descr = mb_ereg_replace('(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~\@!]*(\?\S+)?)?)?)', '<a href="\\1" target="_blank">\\1</a>', $descr);
			$descr = nl2br($descr);

			$thumb = LOCAL_URL.DOWNLOAD_PATH.'/'.$i->getId().'.jpg';
			$aud = LOCAL_URL.DOWNLOAD_PATH.'/'.$i->getId().'.mp3';

			$episodeData[] = ["title" => $i->getTitle(), "author" => $i->getAuthor(), "id" => $i->getId(),
				"description" => $descr, "thumbnail" => $thumb, "audio" => $aud];
		}
	}
	$pug = new Pug\Pug(array('prettyprint' => true));
	$output = $pug->render('views/userPage.pug', array(
		'title' => $title,
		'subdir' => SUBDIR,
		'loggedIn' => $loggedIn,
		'edit' => $edit,
		'user' => $userData,
		'localurl' => LOCAL_URL,
		'episodes' => $episodeData,
		'emailverify' => $emailVerify
	));
	return $output;
}
