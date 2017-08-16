<?php
require_once __DIR__ . '/config.php';
ini_set('max_execution_time', 1200);
// Disable output buffering
if(ob_get_level()){
	ob_end_clean();
}

spl_autoload_register(function($class){
	$classes = explode("\\", $class);
	$class = end($classes);
	if(file_exists(__DIR__ . '/' . $class . ".php")){
		require_once __DIR__ . '/' . $class . '.php';
	}
	else if(file_exists(__DIR__ . '/classes/' . $class . ".php")){
		require_once __DIR__ . '/classes/' . $class . '.php';
	}
	else if(file_exists(__DIR__ . '/classes/DB/' . $class . ".php")){
		require_once __DIR__ . '/classes/DB/' . $class . '.php';
	}
	else if(file_exists(__DIR__ . '/SupportedSites/' . $class . ".php")){
		require_once __DIR__ . '/SupportedSites/' . $class . '.php';
	}
	else if(file_exists(__DIR__ . '/Feeds/' . $class . ".php")){
		require_once __DIR__ . '/Feeds/' . $class . '.php';
	}
});

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
// Update session cookie and push expiration into the future
setcookie(session_name(), session_id(), time() + 2678400, "/", session_get_cookie_params()["domain"],
	session_get_cookie_params()["secure"], session_get_cookie_params()["httponly"]);

// Make download folder if it does not exist and write htaccess file to cache content
if(!file_exists(__DIR__ . "/" . DOWNLOAD_PATH)){
	mkdir(__DIR__ . "/" . DOWNLOAD_PATH);
	file_put_contents(__DIR__ . "/" . DOWNLOAD_PATH . "/.htaccess", "<filesMatch \".(png|jpg|mp3|mp4)$\">
	Header set Cache-Control \"max-age=604800, public\"
	</filesMatch>");
}

/**
 * Deletes all session variables and the session cookies
 */
function clearSession(){
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]
	);
	session_destroy();
	session_write_close();
}

// Download new User from Db
if(userIsLoggedIn()){
	$dal = getDAL();
	try{
		$_SESSION["user"] = $dal->getUserByID($_SESSION["user"]->getUserID());
	}
	catch(Exception $e){
		userLogOut();
	}
}

/**
 * Sets the CHECK_REQUIRED flag in the config file
 *
 * @param bool $checkRequired
 */
function setCheckRequired($checkRequired){
	$currentConfig = file_get_contents(__DIR__ . '/config.php');
	$newConfig = preg_replace("/define\(\"CHECK_REQUIRED\",\s+(true|false)\)/", "define(\"CHECK_REQUIRED\", $checkRequired)", $currentConfig);
	file_put_contents(__DIR__ . '/config.php', $newConfig);
}

if(CHECK_REQUIRED){
	$dal = getDAL();
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
			error_log("Database creation error, verifyDB output: " . $dal->verifyDB());
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
			error_log("Database updating error, verifyDB output: " . $dal->verifyDB());
		}
	}
	else{
		error_log("Unknown database error: " . $nextStep);
	}
}

function SRIChecksum($input){
	$hash = hash('sha256', $input, true);
	$hashBase64 = base64_encode($hash);

	return "sha256-$hashBase64";
}

function userLogIn(\AudioDidact\User $user){
	$_SESSION["loggedIn"] = true;
	$_SESSION["user"] = $user;
}

function userLogOut(){
	$_SESSION["loggedIn"] = false;
	unset($_SESSION["user"]);
	clearSession();
}

function userIsLoggedIn(){
	return (isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && isset($_SESSION["user"]) && $_SESSION["user"] !=
		null);
}

/**
 * Returns Pug (Jade) rendered HTML for a given view and options
 *
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
	if(userIsLoggedIn()){
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

	$initialOptions = [
		'title' => $title,
		'subdir' => SUBDIR,
		'loggedIn' => $loggedin,
		'localurl' => LOCAL_URL,
		'user' => $userData,
		'verified' => $verified,
		'emailEnabled' => EMAIL_ENABLED
	];

	// Allow overwriting keys, but log the problem
	$overWrittenKeys = array_intersect_key($initialOptions, $options);
	if(count($overWrittenKeys) > 0){
		error_log("You are overwriting " . count($overWrittenKeys) . " keys in the Pug options! "
			. implode(", ", array_keys($overWrittenKeys)));
	}

	$options = array_merge($initialOptions, $options);

	$pug = new Pug\Pug(['prettyprint' => $prettyPrint]);

	return $pug->render($view, $options);
}

/**
 * Returns the correct plural or singular form of the given word
 *
 * @param $word String singular form of the word
 * @param $num int number of things the word is referring to
 * @return string correct form of the given word for the input number
 */
function pluralize($word, $num){
	$vowels = ["a", "e", "i", "o", "u"];
	if($num == 1){
		return $word;
	}
	if(mb_substr($word, -1, 1) == "y" && !in_array(mb_substr($word, -2, 1), $vowels, true)){
		return mb_substr($word, 0, mb_strlen($word) - 1) . "ies";
	}
	else if(mb_substr($word, -1, 1) == "s" || mb_substr($word, -1, 1) == "o"){
		return $word . "es";
	}
	else{
		return $word . "s";
	}
}

function stringListicle($list){
	$returnString = "";
	if(count($list) == 0){
		return $returnString;
	}
	if(count($list) == 1){
		return $list[0];
	}
	if(count($list) == 2){
		return $list[0] . " and " . $list[1];
	}
	foreach($list as $i => $item){
		if($i == count($list) - 1){
			$returnString .= "and " . $item;
		}
		else{
			$returnString .= $item . ", ";
		}
	}

	return $returnString;
}

function mb_str_split($string){
	return preg_split('/(?<!^)(?!$)/u', $string);
}

/**
 * Makes a new DAL class based on values in config.php
 *
 * @return \AudioDidact\DB\DAL
 */
function getDAL(){
	$myDalClass = ChosenDAL;

	return new $myDalClass(PDO_STR);
}
