<?php
	require_once __DIR__."/header.php";
	require_once __DIR__."/views/views.php";
	
	function userPage($webID){
		$myDalClass = ChosenDAL;
		$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
		$user = $dal->getUserByWebID($webID);
		if($user == null){
			echo "<script>alert(\"Invalid User!\");window.location = \"/".SUBDIR."\";</script>";
			exit();
		}
			
		echo "
		<!DOCTYPE html>
		<html>";
		if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
			makeHeader($webID." | User Page | Edit");
		}
		else{
			makeHeader($webID." | User Page");
		}
		echo "<body>";
		makeNav();
		echo '<div class="container-fluid">';
		if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && $_SESSION["user"]->getWebID() == $webID){
			makeEditProfile($user);
		}
		else{
			makeViewProfile($user);
		}
		echo "
				</div>
			</body>
		</html>";
	}
?>


