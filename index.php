<?php
require_once __DIR__."/header.php";

if(isset($_GET["manual"])){
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && $_SESSION["user"] != null &&
		$_SESSION["user"]->isEmailVerified()){
		echo generatePug("views/addVideoUpload.pug", "Add Content Manually");
	}else{
		echo generatePug("views/homepage.pug", "Home");
	}
}
else{
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && $_SESSION["user"] != null &&
		$_SESSION["user"]->isEmailVerified()){
		echo generatePug("views/addVideo.pug", "Add Content");
	}else{
		echo generatePug("views/homepage.pug", "Home");
	}
}
