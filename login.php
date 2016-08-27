<?php
/**
 * This file handles the login and logout procedures. It checks the username and password of the user and sets session
 * variables if the information is correct.
 */
require_once __DIR__."/header.php";

// Check if the user is requesting a logout
if(isset($_POST["action"]) && $_POST["action"] == "logout"){
	unset($_SESSION["user"]);
	$_SESSION["loggedIn"] = false;
	$_SESSION = [];
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]
	);
	session_destroy();
	session_write_close();
	echo "Logout Success!";
	exit(0);
}

// Make sure necessary variables are given
if(isset($_POST["uname"]) && isset($_POST["passwd"])){
	// Check login info, set loggedIn to true if the information is correct
	$myDalClass = ChosenDAL;
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
	$possibleUser = $dal->getUserByUsername($_POST["uname"]);
	$possibleUserEmail = $dal->getUserByEmail($_POST["uname"]);
	// Check user based on username
	if($possibleUser != null && $possibleUser->passwdCorrect($_POST["passwd"])){
		$_SESSION["user"] = $possibleUser;
		$_SESSION["loggedIn"] = true;
		echo "Login Success!";
	}
	// Check user based on email
	else if($possibleUserEmail != null && $possibleUserEmail->passwdCorrect($_POST["passwd"])){
		$_SESSION["user"] = $possibleUserEmail;
		$_SESSION["loggedIn"] = true;
		echo "Login Success!";
	}
	else{
		$_SESSION["loggedIn"] = false;
		$_SESSION["user"] = null;
		echo "Login Failed!";
	}
}
else{
	$_SESSION["loggedIn"] = false;
	$_SESSION["user"] = null;
	echo "Login Failed!";
}
