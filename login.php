<?php
/**
 * This file handles the login and logout procedures. It checks the username and password of the user and sets session
 * variables if the information is correct.
 */
require_once __DIR__."/header.php";

// Check if the user is requesting a logout
if(isset($_POST["action"]) && $_POST["action"] == "logout"){
	userLogOut();
	clearSession();
	echo "Logout Success!";
	exit(0);
}

// Make sure necessary variables are given
if(isset($_POST["uname"]) && isset($_POST["passwd"])){
	// Check login info, set loggedIn to true if the information is correct
	$dal = getDAL();
	$possibleUser = $dal->getUserByUsername($_POST["uname"]);
	$possibleUserEmail = $dal->getUserByEmail($_POST["uname"]);
	// Check user based on username
	if($possibleUser != null && $possibleUser->passwdCorrect($_POST["passwd"])){
		userLogIn($possibleUser);
		echo "Login Success!";
	}
	// Check user based on email
	else if($possibleUserEmail != null && $possibleUserEmail->passwdCorrect($_POST["passwd"])){
		userLogIn($possibleUserEmail);
		echo "Login Success!";
	}
	else{
		userLogOut();
		echo "Login Failed!";
	}
}
else{
	userLogOut();
	echo "Login Failed!";
}
