<?php
require_once __DIR__.'/config.php';
ini_set('max_execution_time', 1200);
// Disable output buffering
if (ob_get_level()){
   ob_end_clean();
}

spl_autoload_register(function($class){
	$classes = explode("\\", $class);
	$class = end($classes);
	if(file_exists(__DIR__.'/'.$class.".php")){
		require_once __DIR__.'/'.$class.'.php';
	}
	else if(file_exists(__DIR__.'/classes/'.$class.".php")){
		require_once __DIR__.'/classes/'.$class.'.php';
	}
	else if(file_exists(__DIR__.'/SupportedSites/'.$class.".php")){
		require_once __DIR__.'/SupportedSites/'.$class.'.php';
	}
	else if(file_exists(__DIR__.'/Feeds/'.$class.".php")){
		require_once __DIR__.'/Feeds/'.$class.'.php';
	}
	else{
		error_log("Class ".$class." could not be found!");
	}
});
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");

if (session_status() == PHP_SESSION_NONE) {
	session_set_cookie_params(
		2678400,
		"/",
		parse_url(LOCAL_URL)["host"],
		//HTTPS only
		SessionCookieSecure,
		true
	);
	session_start();
}
// Update session cookie and push expiration into the future
setcookie(session_name(), session_id(), time()+2678400, "/", session_get_cookie_params()["domain"],
	session_get_cookie_params()["secure"], session_get_cookie_params()["httponly"]);

if(!function_exists("clearSession")){
	/**
	 * Deletes all session variables and the session cookies
	 */
	function clearSession(){
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
	}
}

// Download new User from Db
if(isset($_SESSION["user"]) && isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
	$myDalClass = ChosenDAL;
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
	try{
		$_SESSION["user"] = $dal->getUserByID($_SESSION["user"]->getUserID());
	}
	catch(Exception $e){
		clearSession();
	}
}
else if(isset($_SESSION["user"]) && $_SESSION["user"] == null){
	clearSession();
}

if(!function_exists("setCheckRequired")){
	/**
	 * Sets the CHECK_REQUIRED flag in the config file
	 * @param bool $checkRequired
	 */
	function setCheckRequired($checkRequired){
		$currentConfig = file_get_contents("config.php");
		$newConfig = preg_replace("/define\(\"CHECK_REQUIRED\",\s+.*\)/", "define(\"CHECK_REQUIRED\", $checkRequired)", $currentConfig);
		file_put_contents("config.php", $newConfig);
	}
}

if(CHECK_REQUIRED){
	$myDalClass = ChosenDAL;
	$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
	$nextStep = $dal->verifyDB();
	if($nextStep == 0){
		setCheckRequired("false");
	}
	else if($nextStep == 1){
		echo "<h1>The database needs to be created, this will be completed automatically...</h1>";
		error_log("Database needs to be created");
		$dal->makeDB(1);
		if($dal->verifyDB() == 0){
			setCheckRequired("false");
		}
		else{
			error_log("Database creation error, verifyDB output: ".$dal->verifyDB());
		}
	}
	else if($nextStep == 2){
		echo "<h1>The database needs to be updated, this will be completed automatically...</h1>";
		error_log("Database needs to be updated");
		$dal->makeDB(2);
		$dal->verifyDB();
		if($dal->verifyDB() == 0){
			setCheckRequired("false");
		}
		else{
			error_log("Database updating error, verifyDB output: ".$dal->verifyDB());
		}
	}
	else{
		error_log("Unknown database error: ".$nextStep);
	}
}
