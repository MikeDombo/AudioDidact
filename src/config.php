<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Parser;

$yaml = new Parser();
$ymlConfig = $yaml->parse(file_get_contents(__DIR__ . '/config.yml'));

$configVariableNames = ["AD_LOCAL_URL" => ["name" => "local-url", "type" => "string"],
	"AD_SUBDIRECTORY" => ["name" => "subdirectory", "type" => "string"],
	"AD_API_KEYS_GOOGLE" => ["name" => "api-keys_google", "type" => "string"],
	"AD_DOWNLOAD_DIRECTORY" => ["name" => "download-directory", "type" => "string"],
	"AD_SESSION_COOKIE_SECURE" => ["name" => "session-cookie-secure", "type" => "boolean"],
	"AD_EMAIL_FROM" => ["name" => "email_from", "type" => "string"],
	"AD_EMAIL_ENABLED" => ["name" => "email_enabled", "type" => "boolean"],
	"AD_DATABASE_DRIVER" => ["name" => "database_driver", "type" => "string"],
	"AD_DATABASE_ALWAYS_CHECK" => ["name" => "database_always-check", "type" => "boolean"],
	"AD_DATABASE_CONNECTION_STRING" => ["name" => "database_connection-string", "type" => "string"],
	"AD_DATABASE_USER" => ["name" => "database_user", "type" => "string"],
	"AD_DATABASE_PASSWORD" => ["name" => "database_password", "type" => "string"],
	"AD_DATABASE_DATABASE_NAME" => ["name" => "database-name", "type" => "string"]
];

foreach($configVariableNames as $k => $v){
	$e = getenv($k);
	if($e != false){
		if($v["type"] == "boolean"){
			$e = boolval($e);
		}
		if(mb_strpos($k, "DATABASE") > -1 || mb_strpos($k, "EMAIL") > -1 || mb_strpos($k, "API_KEYS") > -1){
			switch($k){
				case("AD_API_KEYS_GOOGLE"):
					$ymlConfig["api-keys"]["google"] = $e;
					break;
				case("AD_EMAIL_FROM"):
					$ymlConfig["email"]["from"] = $e;
					break;
				case("AD_EMAIL_ENABLED"):
					$ymlConfig["email"]["enabled"] = $e;
					break;
				case("AD_DATABASE_DRIVER"):
					$ymlConfig["database"]["driver"] = $e;
					break;
				case("AD_DATABASE_ALWAYS_CHECK"):
					$ymlConfig["database"]["always-check"] = $e;
					break;
				case("AD_DATABASE_CONNECTION_STRING"):
					$ymlConfig["database"]["connection-string"] = $e;
					break;
				case("AD_DATABASE_USER"):
					$ymlConfig["database"]["user"] = $e;
					break;
				case("AD_DATABASE_PASSWORD"):
					$ymlConfig["database"]["password"] = $e;
					break;
				case("AD_DATABASE_DATABASE_NAME"):
					$ymlConfig["database"]["database-name"] = $e;
					break;
				default:
					break;
			}
		}
		else{
			$ymlConfig[$v["name"]] = $e;
		}
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
if($ymlConfig["database"]["always-check"]){
	define("CHECK_REQUIRED", $ymlConfig["database"]["always-check"]);
}
else{
	define("CHECK_REQUIRED", true);
}
