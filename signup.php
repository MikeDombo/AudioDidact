<?php
require_once __DIR__."/header.php";

// Check if a user is signing up or nees the sign up webpage
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
			$user->setFeedText("");
			$user->setPrivateFeed(false);
			$user->setFeedLength(25);
			// Add user to db and set session variables if it is a success.
			try{
				$dal->addUser($user);
				$_SESSION["loggedIn"] = true;
				$_SESSION["user"] = $dal->getUserByUsername($user->getUsername());
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
	// Stop execution so that the sign up webpage is not shown.
	exit(0);
}
require_once(__DIR__."/views/views.php");
?>
<html>
	<?php makeHeader("Sign Up");?>
	<body>
		<script>
			$(function() {
				$("#unameSignup").keypress(function(e) {
					if(e.which == 10 || e.which == 13) {
						signup();
					}
				});
				$("#passwdSignup").keypress(function(e) {
					if(e.which == 10 || e.which == 13) {
						signup();
					}
				});
				$("#email").keypress(function(e) {
					if(e.which == 10 || e.which == 13) {
						signup();
					}
				});
			});
			function signup(){
				$.post("/<?php echo SUBDIR;?>signup.php", {uname:$("#unameSignup").val(), passwd:$("#passwdSignup").val(),
					email:$("#email").val()}, function(data){
					if(data.indexOf("Success")>-1){
						location.assign("/<?php echo SUBDIR;?>getting_started.php");
					}
					else{
						alert(data);
					}
				});
			}
		</script>
		<?php makeNav();?>
		<div id="main-content" class="container-fluid">
			<div class="col-sm-8 offset-sm-2">
				<form class="navbar-form navbar-left">
					<div class="form-group row">
						<label for="email" class="col-form-label col-sm-2">Email Address:</label>
						<div class="col-sm-10">
							<input id="email" type="text" class="form-control" placeholder="Email Address">
						</div>
					</div>
					<div class="form-group row">
						<label for="unameSignup" class="col-form-label col-sm-2">Username:</label>
						<div class="col-sm-10">
							<input id="unameSignup" type="text" class="form-control" placeholder="Username">
						</div>
					</div>
					<div class="form-group row">
						<label for="passwdSignup" class="col-form-label col-sm-2">Password:</label>
						<div class="col-sm-10">
							<input id="passwdSignup" type="password" class="form-control" placeholder="Password">
						</div>
					</div>
					<a class="btn btn-success form-control" style="color:#FFFFFF" href="#" onclick="signup();">Sign Up</a>
				</form>
			</div>
		</div>
	</body>
</html>
