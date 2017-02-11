<?php
	require_once __DIR__."/header.php";
	require_once __DIR__."/views/views.php";

	/**
	 * Generates the account management page
	 * @param string $webID
	 */
	function userPage($webID){
		$myDalClass = ChosenDAL;
		/** @var $dal \DAL */
		$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
		/** @var $user \User */
		$user = $dal->getUserByWebID($webID);
		if($user == null){
			echo "<script>alert(\"Invalid User!\");window.location = \"/".SUBDIR."\";</script>";
			exit();
		}

		echo "
		<!DOCTYPE html>
		<html>";
		if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && $_SESSION["user"]->getWebID() == $webID){
			makeHeader("User Page | $webID | Edit");
		}
		else{
			makeHeader("User Page | $webID");
		}
		echo "<body>";
		makeNav();
		echo '<div class="container-fluid">';
		if(isset($_GET["verifyEmail"]) && !$user->isEmailVerified()){
			$result = $user->verifyEmailVerificationCode($_GET["verifyEmail"]);
			if($result){
				$user->setEmailVerified(1);
				$user->setEmailVerificationCodes([]);
				$dal->updateUser($user);
				echo '<div class="alert alert-success" role="alert">
				<div class="alert-text">Successfully verified email.</div>
				</div>';
			}
			else{
				echo '<div class="alert alert-danger" role="alert">
				<div class="alert-text">Failed to verify email!</div>
				</div>';
			}
		}
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
