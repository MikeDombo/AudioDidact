<?php

use AudioDidact\GlobalFunctions;

require_once __DIR__ . "/header.php";
if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(!isset($_POST["name"]) || !isset($_POST["value"])){
		outputGenericError();
	}
	else{
		if(!GlobalFunctions::userIsLoggedIn()){
			echo json_encode(["success" => false, "error" => "Must be logged in to change data!"]);
		}
		else{
			$dal = GlobalFunctions::getDAL();
			$user = $dal->getUserByID($_SESSION["user"]->getUserID());
			$changeSuccess = true;
			switch($_POST["name"]){
				case "fname":
					$user->setFname(filter_var($_POST["value"], FILTER_SANITIZE_STRING));
					break;
				case "lname":
					$user->setLname(filter_var($_POST["value"], FILTER_SANITIZE_STRING));
					break;
				case "gender":
					if($_POST["value"] == 1 || $_POST["value"] == 2 || $_POST["value"] == 3){
						$user->setGender($_POST["value"]);
					}
					else{
						outputGenericError();
					}
					break;
				case "email":
					if($user->validateEmail($_POST["value"]) && !$dal->emailExists($_POST["value"])){
						$user->setEmail($_POST["value"]);
					}
					else{
						echo json_encode(["success" => false, "error" => "Email invalid or is already registered!"]);
					}
					break;
				case "feedLen":
					if(intval($_POST["value"]) > 0){
						$user->setFeedLength(intval($_POST["value"]));
					}
					else{
						echo json_encode(["success" => false, "error" => "Feed length must be positive!"]);
					}
					break;
				case "privateFeed":
					if($_POST["value"] == "true"){
						$_POST["value"] = true;
					}
					else{
						$_POST["value"] = false;
					}
					$user->setPrivateFeed($_POST["value"]);
					break;
				case "webID":
					if(!$dal->webIDExists($_POST["value"])){
						if(!$user->validateWebID($_POST["value"])){
							echo json_encode(["success" => false, "error" => "Custom URL contains invalid characters!"]);
						}
						else{
							$user->setWebID($_POST["value"]);
						}
					}
					else{
						echo json_encode(["success" => false, "error" => "Custom URL is already registered!"]);
					}
					break;
				case "feedTitle":
					if($user->validateName($_POST["value"])){
						$current = $user->getFeedDetails();
						$current["title"] = $_POST["value"];
						$user->setFeedDetails($current);
					}
					else{
						echo json_encode(["success" => false, "error" => "Title contains illegal characters"]);
					}
					break;
				case "feedDesc":
					$current = $user->getFeedDetails();
					$current["description"] = $_POST["value"];
					$user->setFeedDetails($current);
					break;
				case "feedIco":
					if(filter_var($_POST["value"], FILTER_VALIDATE_URL)){
						$current = $user->getFeedDetails();
						$current["icon"] = $_POST["value"];
						$user->setFeedDetails($current);
					}
					else{
						echo json_encode(["success" => false, "error" => "Image is not a valid URL"]);
					}
					break;
				case "itunesAuthor":
					if($user->validateName($_POST["value"])){
						$current = $user->getFeedDetails();
						$current["itunesAuthor"] = $_POST["value"];
						$user->setFeedDetails($current);
					}
					else{
						echo json_encode(["success" => false, "error" => "Author contains illegal characters!"]);
					}
					break;
				default:
					$changeSuccess = false;
					outputGenericError();
					break;
			}
			if($changeSuccess){
				$dal->updateUser($user);
				outputSuccess($user);
			}
		}
	}
}
else if(isset($_GET["resend"])){
	echo "<script>";
	if(GlobalFunctions::userIsLoggedIn() && !$_SESSION["user"]->isEmailVerified() && EMAIL_ENABLED){
		$_SESSION["user"]->addEmailVerificationCode();
		$dal = GlobalFunctions::getDAL();
		$dal->updateUserEmailPasswordCodes($_SESSION["user"]);
		AudioDidact\EMail::sendVerificationEmail($_SESSION["user"]);
		echo 'alert("Verification email resent!");';
	}
	else{
		echo 'alert("Verification email failed.");';
	}
	echo 'location.assign("/' . SUBDIR . '");';
	echo "</script>";
}

/**
 * Output json encoded array that success is true
 * Updates the session user variable
 *
 * @param \AudioDidact\User $user
 */
function outputSuccess(\AudioDidact\User $user){
	GlobalFunctions::userLogIn($user);
	echo json_encode(["success" => true]);
}

/**
 * Outputs generic json encoded failure
 */
function outputGenericError(){
	echo json_encode(["success" => false, "error" => "Invalid Data Received!"]);
}
