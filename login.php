<?php
/**
 * This file handles the login and logout procedures. It checks the username and password of the user and sets session
 * variables if the information is correct.
 */

spl_autoload_register(function($class){
	require_once __DIR__.'/config.php';
	require_once __DIR__.'/classes/MySQLDAL.php';
	require_once __DIR__.'/classes/User.php';
});

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

if(isset($_POST["action"])){
	if($_POST["action"] == "logout"){
		$_SESSION["user"] = null;
		$_SESSION["loggedIn"] = false;
		echo "Logout Success!";
		exit(0);
	}
}
if(isset($_POST["uname"]) && isset($_POST["passwd"])){
	// Check login info, set loggedIn to true if the information is correct
	$dal = new MySQLDAL(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
	$possibleUser = $dal->getUserByUsername($_POST["uname"]);
	$possibleUserEmail = $dal->getUserByEmail($_POST["uname"]);
	if($possibleUser != null && $possibleUser->passwdCorrect($_POST["passwd"])){
		$_SESSION["user"] = $possibleUser;
		$_SESSION["loggedIn"] = true;
		echo "Login Success!";
	}
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
?>