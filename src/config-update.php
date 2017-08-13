<?php
$configFile = "config.php";

if(php_sapi_name() != "cli"){
	exit(1);
}

if(file_exists($configFile)){
	$currentConfig = file_get_contents($configFile);
	$newConfig = preg_replace("/define\(\"CHECK_REQUIRED\",\s+.*\)/", "define(\"CHECK_REQUIRED\", true)", $currentConfig);
	file_put_contents($configFile, $newConfig);
}
else{
	die("$configFile does not exist, cannot update what does not exist!");
}
