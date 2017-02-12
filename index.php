<?php
require_once __DIR__."/header.php";

if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && $_SESSION["user"]->isEmailVerified()){
	echo generatePug("views/addVideo.pug", "Add Content");
}
else if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && !$_SESSION["user"]->isEmailVerified()){
	echo generatePug("views/homepage.pug", "Add Content");
}
else{
	echo generatePug("views/homepage.pug", "Home");
}
