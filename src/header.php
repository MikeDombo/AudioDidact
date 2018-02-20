<?php
use \AudioDidact\GlobalFunctions;

require_once __DIR__ . '/config.php';
ini_set('max_execution_time', 1200);
// Disable output buffering
if(ob_get_level()){
	ob_end_clean();
}

// Check if we should be forcing HTTPS
if(FORCE_HTTPS && !is_ssl()){
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

if(session_status() == PHP_SESSION_NONE){
	session_set_cookie_params(
		2678400,
		"/",
		parse_url(LOCAL_URL)["host"],
		//HTTPS only
		SESSION_COOKIE_SECURE,
		true
	);
	session_start();
}
else{
	// Update session cookie and push expiration into the future
	setcookie(session_name(), session_id(), time() + 2678400, "/", session_get_cookie_params()["domain"],
		SESSION_COOKIE_SECURE, true);
}

// Generate a cookie for CSRF prevention
if(!isset($_COOKIE["AD_CSRF"])){
	$csrfRandom = GlobalFunctions::randomToken();
	setcookie("AD_CSRF", $csrfRandom, 0, "/", parse_url(LOCAL_URL)["host"],
		SESSION_COOKIE_SECURE, true);

	$_COOKIE["AD_CSRF"] = $csrfRandom; // Set it locally so that it is immediately usable
}

// Make download folder if it does not exist and write htaccess file to cache content
if(!file_exists(__DIR__ . "/" . DOWNLOAD_PATH)){
	mkdir(__DIR__ . "/" . DOWNLOAD_PATH);
	file_put_contents(__DIR__ . "/" . DOWNLOAD_PATH . "/.htaccess", "<filesMatch \".(png|jpg|mp3|mp4)$\">
	Header set Cache-Control \"max-age=604800, public\"
	</filesMatch>");
}

// Download new User from Db
if(GlobalFunctions::userIsLoggedIn()){
	$dal = GlobalFunctions::getDAL();
	try{
		$_SESSION["user"] = $dal->getUserByID($_SESSION["user"]->getUserID());
	}
	catch(Exception $e){
		GlobalFunctions::userLogOut();
	}
}

if(CHECK_REQUIRED){
	$dal = GlobalFunctions::getDAL();
	$nextStep = $dal->verifyDB();
	if($nextStep == 0){
		GlobalFunctions::setCheckRequired("false");
	}
	else if($nextStep == 1){
		echo "<h1>The database needs to be created, this will be completed automatically...</h1>";
		error_log("Database needs to be created");
		$dal->makeDB(1);
		if($dal->verifyDB() == 0){
			GlobalFunctions::setCheckRequired("false");
		}
		else{
			error_log("Database creation error, verifyDB output: " . $dal->verifyDB());
		}
	}
	else if($nextStep == 2){
		echo "<h1>The database needs to be updated, this will be completed automatically...</h1>";
		error_log("Database needs to be updated");
		$dal->makeDB(2);
		$dal->verifyDB();
		if($dal->verifyDB() == 0){
			GlobalFunctions::setCheckRequired("false");
		}
		else{
			error_log("Database updating error, verifyDB output: " . $dal->verifyDB());
		}
	}
	else{
		error_log("Unknown database error: " . $nextStep);
	}
}
