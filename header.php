<?php
require_once __DIR__.'/config.php';
ini_set('max_execution_time', 1200);
spl_autoload_register(function($class){
	$class = end(explode("\\", $class));
	if(file_exists(__DIR__.'/'.$class.".php")){
		require_once __DIR__.'/'.$class.'.php';
	}
	else if(file_exists(__DIR__.'/classes/'.$class.".php")){
		require_once __DIR__.'/classes/'.$class.'.php';
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


$sessionCookieIsHTTPS = true;
if (session_status() == PHP_SESSION_NONE) {
	session_set_cookie_params(
		2678400,
		"/",
		parse_url(LOCAL_URL)["host"],
		$sessionCookieIsHTTPS, //HTTPS only
		true
	);
	session_start();
}
setcookie(session_name(),session_id(),time()+2678400, "/", session_get_cookie_params()["domain"], session_get_cookie_params()["secure"], session_get_cookie_params()["httponly"]);
