<?php
$globals = ["LOCAL_URL" => "https://localhost/podtube/src", "SUBDIR" => "podtube/src/", "GOOGLE_API_KEY" => "****",
	"DOWNLOAD_PATH" => "temp", "SESSION_COOKIE_SECURE" => true,
	"EMAIL_FROM" => "\"AudioDidact Administrator\"<michael@mikedombrowski.com>",
	"ChosenDAL" => "\\AudioDidact\\MySQLDAL", "DB_USER" => "root", "DB_PASSWORD" => "root",
	"PDO_STR" => "mysql:host=mysql;dbname=podtube;charset=utf8", "CHECK_REQUIRED" => true];

$configFile = "config.php";

if(php_sapi_name() != "cli"){
	exit(1);
}

if(file_exists($configFile)){
	require($configFile);
	foreach($globals as $k => $v){
		if(!defined($k)){
			file_put_contents($configFile, "\r\ndefine(\"$k\", \"$v\");\r\n", FILE_APPEND);
		}
	}
	if(defined("CHECK_REQUIRED") && !CHECK_REQUIRED){
		$currentConfig = file_get_contents($configFile);
		$newConfig = preg_replace("/define\(\"CHECK_REQUIRED\",\s+.*\)/", "define(\"CHECK_REQUIRED\", true)", $currentConfig);
		file_put_contents($configFile, $newConfig);
	}
}
else{
	die("config.php does not exist, cannot update what does not exist!");
}
