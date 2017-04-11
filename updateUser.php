<?php
require_once __DIR__."/header.php";
if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(isset($_POST["name"]) && isset($_POST["value"])){
		if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
			$myDalClass = ChosenDAL;
			/** @var \AudioDidact\DAL $dal */
			$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
			$user = $dal->getUserByID($_SESSION["user"]->getUserID());

			if($_POST["name"] == "fname"){
				$user->setFname(filter_var($_POST["value"], FILTER_SANITIZE_STRING));
				$dal->updateUser($user);
				outputSuccess($user);
			}else if($_POST["name"] == "lname"){
				$user->setLname(filter_var($_POST["value"], FILTER_SANITIZE_STRING));
				$dal->updateUser($user);
				outputSuccess($user);
			}else if($_POST["name"] == "gender"){
				if($_POST["value"] == 1 || $_POST["value"] == 2 || $_POST["value"] == 3){
					$user->setGender($_POST["value"]);
					$dal->updateUser($user);
					outputSuccess($user);
				}else{
					outputGenericError();
				}
			}else if($_POST["name"] == "email"){
				if($user->validateEmail($_POST["value"]) && !$dal->emailExists($_POST["value"])){
					$user->setEmail($_POST["value"]);
					$dal->updateUser($user);
					outputSuccess($user);
				}else{
					echo json_encode(["success" => false, "error" => "Email invalid or is already registered!"]);
				}
			}else if($_POST["name"] == "feedLen"){
				if(intval($_POST["value"]) > 0){
					$user->setFeedLength(intval($_POST["value"]));
					$dal->updateUser($user);
					outputSuccess($user);
				}else{
					echo json_encode(["success" => false, "error" => "Feed length must be positive!"]);
				}
			}else if($_POST["name"] == "privateFeed"){
				if($_POST["value"] == "true"){
					$_POST["value"] = true;
				}else{
					$_POST["value"] = false;
				}
				$user->setPrivateFeed($_POST["value"]);
				$dal->updateUser($user);
				outputSuccess($user);
			}else if($_POST["name"] == "webID"){
				if(!$dal->webIDExists($_POST["value"])){
					if(!$user->validateWebID($_POST["value"])){
						echo json_encode(["success" => false, "error" => "Custom URL contains invalid characters!"]);
					}else{
						$user->setWebID($_POST["value"]);
						$dal->updateUser($user);
						outputSuccess($user);
					}
				}else{
					echo json_encode(["success" => false, "error" => "Custom URL is already registered!"]);
				}
			}else if($_POST["name"] == "feedTitle"){
				if($user->validateName($_POST["value"])){
					$current = $user->getFeedDetails();
					$current["title"] = $_POST["value"];
					$user->setFeedDetails($current);
					$dal->updateUser($user);
					outputSuccess($user);
				}else{
					echo json_encode(["success" => false, "error" => "Title contains illegal characters"]);
				}
			}else if($_POST["name"] == "feedDesc"){
				$current = $user->getFeedDetails();
				$current["description"] = $_POST["value"];
				$user->setFeedDetails($current);
				$dal->updateUser($user);
				outputSuccess($user);
			}else if($_POST["name"] == "feedIco"){
				if(filter_var($_POST["value"], FILTER_VALIDATE_URL)){
					$current = $user->getFeedDetails();
					$current["icon"] = $_POST["value"];
					$user->setFeedDetails($current);
					$dal->updateUser($user);
					outputSuccess($user);
				}else{
					echo json_encode(["success" => false, "error" => "Image is not a valid URL"]);
				}
			}else if($_POST["name"] == "itunesAuthor"){
				if($user->validateName($_POST["value"])){
					$current = $user->getFeedDetails();
					$current["itunesAuthor"] = $_POST["value"];
					$user->setFeedDetails($current);
					$dal->updateUser($user);
					outputSuccess($user);
				}else{
					echo json_encode(["success" => false, "error" => "Author contains illegal characters!"]);
				}
			}else{
				outputGenericError();
			}
		}else{
			echo json_encode(["success" => false, "error" => "Must be logged in to change data!"]);
		}
	}else{
		outputGenericError();
	}
}
else if(isset($_GET["resend"])){
	echo "<script>";
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] &&
		!$_SESSION["user"]->isEmailVerified()){
		$_SESSION["user"]->addEmailVerificationCode();
		$myDalClass = ChosenDAL;
		$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
		/** @var $dal \AudioDidact\DAL */
		$dal->updateUserEmailPasswordCodes($_SESSION["user"]);
		AudioDidact\EMail::sendVerificationEmail($_SESSION["user"]);
		echo 'alert("Verification email resent!");';
	}
	else{
		echo 'alert("Verification email failed.");';
	}
	echo 'location.assign("/'.SUBDIR.'");';
	echo "</script>";
}
/**
 * Output json encoded array that success is true
 * Updates the session user variable
 * @param \AudioDidact\User $user
 */
function outputSuccess(\AudioDidact\User $user){
	$_SESSION["user"] = $user;
	$_SESSION["loggedIn"] = true;
	echo json_encode(["success"=>true]);
}

/**
 * Outputs generic json encoded failure
 */
function outputGenericError(){
	echo json_encode(["success"=>false, "error"=>"Invalid Data Received!"]);
}
