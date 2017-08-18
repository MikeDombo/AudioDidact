<?php
require __DIR__ . '/vendor/autoload.php';
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

use Symfony\Component\Yaml\Parser;

$yaml = new Parser();
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
$ymlConfig = $yaml->parse(file_get_contents($configFile));

date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");

$environmentVariablePrefix = "AD_";
$configVariableNames = ["LOCAL_URL" => ["name" => "local-url", "type" => "string"],
	"SUBDIRECTORY" => ["name" => "subdirectory", "type" => "string"],
	"API_KEYS_GOOGLE" => ["name" => "api-keys_google", "type" => "string"],
	"DOWNLOAD_DIRECTORY" => ["name" => "download-directory", "type" => "string"],
	"SESSION_COOKIE_SECURE" => ["name" => "session-cookie-secure", "type" => "boolean"],
	"EMAIL_FROM" => ["name" => "email_from", "type" => "string"],
	"EMAIL_ENABLED" => ["name" => "email_enabled", "type" => "boolean"],
	"DATABASE_DRIVER" => ["name" => "database_driver", "type" => "string"],
	"DATABASE_ALWAYS_CHECK" => ["name" => "database_always-check", "type" => "boolean"],
	"DATABASE_CONNECTION_STRING" => ["name" => "database_connection-string", "type" => "string"],
	"DATABASE_USER" => ["name" => "database_user", "type" => "string"],
	"DATABASE_PASSWORD" => ["name" => "database_password", "type" => "string"],
	"DATABASE_DATABASE_NAME" => ["name" => "database_database-name", "type" => "string"]
];

foreach($configVariableNames as $k => $v){
	$k = $environmentVariablePrefix . $k;
	$kk = explode("_", $v["name"]);
	$e = getenv($k);
	if($e != false){
		if($v["type"] == "boolean"){
			$e = mb_strtolower($e) == "true" ? true : false;
		}
		$ymlConfig = \AudioDidact\GlobalFunctions::deepSetDictionaryValues($ymlConfig, $kk, $e);
	}
}

define("LOCAL_URL", $ymlConfig["local-url"]);
define("SUBDIR", $ymlConfig["subdirectory"]);
define("GOOGLE_API_KEY", $ymlConfig["api-keys"]["google"]);
define("DOWNLOAD_PATH", $ymlConfig["download-directory"]);
define("SESSION_COOKIE_SECURE", $ymlConfig["session-cookie-secure"]);
define("EMAIL_FROM", $ymlConfig["email"]["from"]);
define("EMAIL_ENABLED", $ymlConfig["email"]["enabled"]);

$dbData = $ymlConfig["database"];
switch(strtolower($dbData["driver"])){
	case("mysql"):
		define("ChosenDAL", "\\AudioDidact\\DB\\MySQLDAL");
		define("DB_USER", $dbData["user"]);
		define("DB_PASSWORD", $dbData["password"]);
		define("PDO_STR", $dbData["connection-string"]);
		break;
	case("sqlite"):
		define("ChosenDAL", "\\AudioDidact\\DB\\SQLite");
		define("PDO_STR", $dbData["connection-string"]);
		break;
	case("mongodb"):
		define("ChosenDAL", "\\AudioDidact\\DB\\MongoDBDAL");
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
