<?php
require_once __DIR__."/header.php";

// Check if a user is signing up or needs the sign up webpage
if($_SERVER['REQUEST_METHOD'] == "POST"){
	// Check that required variables are present and are not empty.
	// More validation should be completed after this step, ie. check that email is legit.
	if(isset($_POST["uname"]) && isset($_POST["passwd"]) && isset($_POST["email"])){
		$dal = getDAL();

		$u = new AudioDidact\User();
		$statement = $u->signup($_POST["uname"], $_POST["passwd"],  $_POST["email"], $dal);
		echo $statement;
		if(mb_strpos($statement, "failed") > -1){
			userLogOut();
		}
		else{
			$u = $dal->getUserByUsername($u->getUsername());
			userLogIn($u);
		}
	}
	else{
		userLogOut();
		echo "Sign Up Failed!\nNo email, username, or password specified!";
	}
}
else{
	echo generatePug('views/signup.pug', 'Sign Up for AudioDidact');
}
