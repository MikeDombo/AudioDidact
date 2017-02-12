<?php
require_once __DIR__."/header.php";

if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && $_SESSION["user"]->isEmailVerified()){
	$loggedin = "false";
	$userData = [];
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
		$loggedin = "true";
		$user = $_SESSION["user"];
		$userData = ["privateFeed"=>$user->isPrivateFeed(), "fName"=>$user->getFname(), "lName"=>$user->getLname(),
			"gender"=>$user->getGender(), "webID"=>$user->getWebID(), "username"=>$user->getUsername(),
			"email"=>$user->getEmail(), "feedLength"=>$user->getFeedLength(), "feedDetails"=>$user->getFeedDetails()
		];
	}
	$pug = new Pug\Pug(array('prettyprint' => true));
	$output = $pug->render('views/addVideo.pug', array(
		'title' => "Add Content",
		'subdir' => SUBDIR,
		'loggedIn' => $loggedin,
		'localurl' => LOCAL_URL,
		'user' => $userData,
		'verified' => true
	));
	echo $output;
}
else if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && !$_SESSION["user"]->isEmailVerified()){
	$user = $_SESSION["user"];
	$userData = ["privateFeed"=>$user->isPrivateFeed(), "fName"=>$user->getFname(), "lName"=>$user->getLname(),
		"gender"=>$user->getGender(), "webID"=>$user->getWebID(), "username"=>$user->getUsername(),
		"email"=>$user->getEmail(), "feedLength"=>$user->getFeedLength(), "feedDetails"=>$user->getFeedDetails()
	];
	$pug = new Pug\Pug(array('prettyprint' => true));
	$output = $pug->render('views/homepage.pug', array(
		'title' => "Add Content",
		'subdir' => SUBDIR,
		'loggedIn' => true,
		'localurl' => LOCAL_URL,
		'user' => $userData,
		'verified' => false
	));
	echo $output;
}
else{
	$loggedin = "false";
	$userData = [];
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
		$loggedin = "true";
		$user = $_SESSION["user"];
		$userData = ["privateFeed"=>$user->isPrivateFeed(), "fName"=>$user->getFname(), "lName"=>$user->getLname(),
			"gender"=>$user->getGender(), "webID"=>$user->getWebID(), "username"=>$user->getUsername(),
			"email"=>$user->getEmail(), "feedLength"=>$user->getFeedLength(), "feedDetails"=>$user->getFeedDetails()
		];
	}

	$pug = new Pug\Pug(array('prettyprint' => true));
	$output = $pug->render('views/homepage.pug', array(
		'title' => "Home",
		'subdir' => SUBDIR,
		'loggedIn' => $loggedin,
		'localurl' => LOCAL_URL,
		'user' => $userData,
		'verified' => true
	));
	echo $output;
}
