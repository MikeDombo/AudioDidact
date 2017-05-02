<?php
require_once __DIR__.'/config.php';
require __DIR__ . '/vendor/autoload.php';
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

// Make download folder if it does not exist and write htaccess file to cache content
if(!file_exists(DOWNLOAD_PATH)){
	mkdir(DOWNLOAD_PATH);
	file_put_contents(DOWNLOAD_PATH."/.htaccess", "<filesMatch \".(jpg|mp3|mp4)$\">
	Header set Cache-Control \"max-age=604800, public\"
	</filesMatch>");
}

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
	/** @var \AudioDidact\DAL $dal */
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

function SRIChecksum($input) {
    $hash = hash('sha256', $input, true);
    $hash_base64 = base64_encode($hash);
    return "sha256-$hash_base64";
}

/**
 * Returns Pug (Jade) rendered HTML for a given view and options
 * @param $view string Name of Pug view to be rendered
 * @param $title string Title of the webpage
 * @param array $options Additional options needed to render the view
 * @param bool $prettyPrint If prettyPrint is false, all HTML is on a single line
 * @return string Pug generated HTML
 */
function generatePug($view, $title, $options = [], $prettyPrint = false){
	$loggedin = "false";
	$verified = true;
	$userData = [];
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && isset($_SESSION["user"]) && $_SESSION["user"] != null){
		$loggedin = "true";
		/** @var \AudioDidact\User $user */
		$user = $_SESSION["user"];
		$userData = ["privateFeed" => $user->isPrivateFeed(), "fName" => $user->getFname(), "lName" => $user->getLname(),
			"gender" => $user->getGender(), "webID" => $user->getWebID(), "username" => $user->getUsername(),
			"email" => $user->getEmail(), "feedLength" => $user->getFeedLength(), "feedDetails" => $user->getFeedDetails()
		];
		if(!$user->isEmailVerified()){
			$verified = false;
		}
	}

	$initialOptions = array(
		'title' => $title,
		'subdir' => SUBDIR,
		'loggedIn' => $loggedin,
		'localurl' => LOCAL_URL,
		'user' => $userData,
		'verified' => $verified
	);

	$options = array_merge($initialOptions, $options);

	$pug = new Pug\Pug(array('prettyprint' => $prettyPrint));
	return $pug->render($view, $options);
}

/**
 * Returns the correct plural or singular form of the given word
 * @param $word String singular form of the word
 * @param $num int number of things the word is referring to
 * @return string correct form of the given word for the input number
 */
function pluralize($word, $num){
	$vowels = ["a", "e", "i", "o", "u"];
	if($num == 1){
		return $word;
	}
	if(substr($word, -1, 1) == "y" && !in_array(substr($word, -2, 1), $vowels, true)){
		return substr($word, 0, strlen($word)-1)."ies";
	}
	else if(substr($word, -1, 1) == "s"){
		return $word."es";
	}
	else{
		return $word."s";
	}
}

function stringListicle($list){
	$returnString = "";
	if(count($list) == 2){
		return $list[0]." and ".$list[1];
	}
	foreach($list as $i=>$item){
		if($i == count($list) - 1){
			$returnString .= "and ".$item;
		}
		else{
			$returnString .= $item.", ";
		}
	}
	return $returnString;
}
