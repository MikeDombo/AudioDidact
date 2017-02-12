<?php
require_once __DIR__."/header.php";

// Check if a user is signing up or needs the sign up webpage
if($_SERVER['REQUEST_METHOD'] == "POST"){
	// Check that required variables are present and are not empty.
	// More validation should be completed after this step, ie. check that email is legit.
	if(isset($_POST["uname"]) && isset($_POST["passwd"]) && isset($_POST["email"])
	&& trim($_POST["uname"]) != "" && trim($_POST["passwd"]) != "" && trim($_POST["email"] != "")){
		$username = $_POST["uname"];
		$password = $_POST["passwd"];
		$email = $_POST["email"];

		if(mb_strlen($password) < 6){
			echo "Sign up failed!\nPassword must be at least 6 characters long!";
			exit(0);
		}

		$myDalClass = ChosenDAL;
		$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
		/** @var $dal \DAL */
		// Make sure the username and email address are not taken.
		if(!$dal->emailExists($email) && !$dal->usernameExists($username)){
			$user = new User();
			if(!$user->validateEmail($email)){
				echo "Sign up failed!\nInvalid Email Address!";
				exit(0);
			}
			if(!$user->validateWebID($username)){
				echo "Sign up failed!\nUsername contains invalid characters!";
				exit(0);
			}

			$user->setUsername($username);
			$user->setEmail($email);
			$user->setPasswd($password);
			$user->setWebID($user->getUsername());
			$user->setPrivateFeed(false);
			$user->setFeedLength(25);
			$podtube = new PodTube($dal, $user);
			$user->setFeedText($podtube->makeFullFeed(true)->generateFeed());
			$user->setEmailVerified(0);
			// Add user to db and set session variables if it is a success.
			try{
				$dal->addUser($user);
				$_SESSION["loggedIn"] = true;
				$user = $dal->getUserByUsername($user->getUsername());
				$user->addEmailVerificationCode();
				$_SESSION["user"] = $user;
				$dal->updateUserEmailPasswordCodes($user);
				EMail::sendVerificationEmail($user);
				echo "Sign Up Success!";
			}
			catch(Exception $e){
				error_log($e);
				$_SESSION["loggedIn"] = false;
				echo "Sign Up Failed!";
			}
		}
		else{
			$_SESSION["loggedIn"] = false;
			echo "Sign Up Failed, username or email already in use!";
		}
	}
	else{
		$_SESSION["loggedIn"] = false;
		echo "Sign Up Failed!\nNo email, username, or password specified!";
	}
}
else{
	echo generatePug('views/signup.pug', 'Sign Up for AudioDidact');
}
