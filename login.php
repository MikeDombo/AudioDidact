<?php
spl_autoload_register(function($class){
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
	$db = "podtube";
	$dbUser = "podtube";
	$dbPass = "podtube";
	$dal = new MySQLDAL("localhost", $db, $dbUser, $dbPass);
	$possibleUser = $dal->getUserByUsername($_POST["uname"]);
	if($possibleUser != null && $possibleUser->passwdCorrect($_POST["passwd"])){
		$_SESSION["user"] = $possibleUser;
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