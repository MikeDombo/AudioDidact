<?php
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");

require_once __DIR__ . '/vendor/autoload.php';
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

$configFile = __DIR__ . '/config.yml';
$configFile2 = __DIR__ . '/../config.yml';
if(!file_exists($configFile)){
	if(file_exists($configFile2)){
		$configFile = $configFile2;
	}
	else{
		die("Unable to load config.yml. Please check that the config file is located in the right place.");
	}
}
define("CONFIG_FILE", $configFile);

function ymlOrEnvParse($configFile, $environmentVariablePrefix = "AD_"){
	$yaml = new Symfony\Component\Yaml\Parser();
	$ymlConfig = $yaml->parse(file_get_contents($configFile));

	$configVariableNames = [
		"API_KEYS_GOOGLE" => ["name" => "api-keys_google", "type" => "string"],
		"DOWNLOAD_DIRECTORY" => ["name" => "download-directory", "type" => "string"],
		"FORCE_HTTPS" => ["name" => "force-https", "type" => "boolean"],
		"EMAIL_FROM" => ["name" => "email_from", "type" => "string"],
		"EMAIL_ENABLED" => ["name" => "email_enabled", "type" => "boolean"],
		"DATABASE_DRIVER" => ["name" => "database_driver", "type" => "string"],
		"DATABASE_ALWAYS_CHECK" => ["name" => "database_always-check", "type" => "boolean"],
		"DATABASE_CONNECTION_STRING" => ["name" => "database_connection-string", "type" => "string"],
		"DATABASE_USER" => ["name" => "database_user", "type" => "string"],
		"DATABASE_PASSWORD" => ["name" => "database_password", "type" => "string"],
		"DATABASE_DATABASE_NAME" => ["name" => "database_database-name", "type" => "string"],
		"SUPPORTED_SITES_CRTV" => ["name" => "supported-sites_crtv", "type" => "string"]
	];

	foreach($configVariableNames as $k => $v){
		$k = $environmentVariablePrefix . $k;
		$kk = explode("_", $v["name"]);
		$e = getenv($k);
		if($e !== false){
			if($v["type"] == "boolean"){
				$e = mb_strtolower($e) == "true";
			}
			$ymlConfig = \AudioDidact\GlobalFunctions::deepSetDictionaryValues($ymlConfig, $kk, $e);
		}
	}
	return $ymlConfig;
}

$ymlConfig = ymlOrEnvParse($configFile);

define("FORCE_HTTPS", $ymlConfig["force-https"]);
define("SESSION_COOKIE_SECURE", \AudioDidact\GlobalFunctions::is_ssl());
define("GOOGLE_API_KEY", $ymlConfig["api-keys"]["google"]);
define("DOWNLOAD_PATH", $ymlConfig["download-directory"]);
define("EMAIL_FROM", $ymlConfig["email"]["from"]);
define("EMAIL_ENABLED", $ymlConfig["email"]["enabled"]);
define("SUPPORTED_SITES_CRTV", $ymlConfig["supported-sites"]["crtv"]);

// Figure out what subdirectory we are in
function getRootSubdirectory(){
	$path = explode("/", $_SERVER["PHP_SELF"]);
	array_pop($path); // Remove PHP file from path
	array_shift($path); // Removing beginning slash
	$subdir =  implode("/", $path);
	// If we're in a subdirectory, end with a trailing slash
	if(!empty($subdir)){
		$subdir .= "/";
	}
	return $subdir;
}

define("SUBDIR", getRootSubdirectory());

// Fix for commandline running
if(php_sapi_name() === "cli"){
	if(!isset($_SERVER["HTTP_HOST"])){
		$_SERVER["HTTP_HOST"] = "localhost";
	}
	if(!isset($_SERVER["REQUEST_URI"])){
		$_SERVER["REQUEST_URI"] = "";
	}
	if(!isset($_SERVER["HTTPS"])){
		$_SERVER["HTTPS"] = "on";
	}
}

// Use the subdir to set the full LOCAL_URL
function getLocalURL($subdir){
	$protocol = \AudioDidact\GlobalFunctions::is_ssl() ? "https://" : "http://";
	$localURL = $protocol . $_SERVER["HTTP_HOST"] . "/" . $subdir;
	// Always end with a trailing slash
	if(mb_substr($localURL, -1, 1) != "/"){
		$localURL .= "/";
	}
	return $localURL;
}

define("LOCAL_URL", getLocalURL(SUBDIR));

$dbData = $ymlConfig["database"];
switch(strtolower($dbData["driver"])){
	case("mysql"):
		define("CHOSEN_DAL", "\\AudioDidact\\DB\\MySQLDAL");
		define("DB_USER", $dbData["user"]);
		define("DB_PASSWORD", $dbData["password"]);
		define("PDO_STR", $dbData["connection-string"]);
		break;
	case("sqlite"):
		define("CHOSEN_DAL", "\\AudioDidact\\DB\\SQLite");
		define("PDO_STR", $dbData["connection-string"]);
		break;
	case("mongodb"):
		define("CHOSEN_DAL", "\\AudioDidact\\DB\\MongoDBDAL");
		define("DB_DATABASE", $dbData["database-name"]);
		define("PDO_STR", $dbData["connection-string"]);
		break;
	default:
		throw new \Exception("Unknown database driver!");
}

//
//
// Do not manually modify below this line
//
//
/** Defines if a database validation is necessary */
if(!empty($ymlConfig["database"]["always-check"])){
	define("CHECK_REQUIRED", $ymlConfig["database"]["always-check"]);
}
else{
	define("CHECK_REQUIRED", true);
}
