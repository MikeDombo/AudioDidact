<?php
require_once __DIR__."/header.php";

// Check if a user is signing up or nees the sign up webpage
if($_SERVER['REQUEST_METHOD'] == "POST"){
	// Check that required variables are present and are not empty.
	// More validation should be completed after this step, ie. check that email is legit.
	if(isset($_POST["uname"]) && isset($_POST["passwd"]) && isset($_POST["email"])
	&& trim($_POST["uname"]) != "" && trim($_POST["passwd"]) != "" && trim($_POST["email"] != "")){
		$myDalClass = ChosenDAL;
		$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
		// Make sure the username and email address are not taken.
		if(!$dal->emailExists($_POST["email"]) && !$dal->usernameExists($_POST["uname"])){
			$user = new User();
			$user->setUsername($_POST["uname"]);
			$user->setEmail($_POST["email"]);
			$user->setPasswd($_POST["passwd"]);
			$user->setWebID($user->getUsername());
			$user->setFeedLength(50);
			// Add user to db and set session variables if it is a success.
			try{
				$dal->addUser($user);
				$_SESSION["loggedIn"] = true;
				$_SESSION["user"] = $user;
				echo "Login Success!";
			}
			catch(Exception $e){
				error_log($e);
				$_SESSION["loggedIn"] = false;
				echo "Login Failed!";
			}
		}
		else{
			$_SESSION["loggedIn"] = false;
			echo "Sign Up Failed, username or email already in use!";
		}
	}
	else{
		$_SESSION["loggedIn"] = false;
		echo "Login Failed!";
	}
	// Stop execution so that the sign up webpage is not shown.
	exit(0);
}
require_once("views".DIRECTORY_SEPARATOR."views.php");
?>
<html>
	<?php makeHeader("Sign Up");?>
	<body>
		<script>
			function validateEmail(email) {
				var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				return re.test(email);
			}
			function signup(){
				$.post("/<?php echo SUBDIR;?>signup.php", {uname:$("#unameSignup").val(), passwd:$("#passwdSignup").val(),
					email:$("#email").val()}, function(data){
					console.log(data);
					if(data.indexOf("Success")>-1){
						location.assign("/<?php echo SUBDIR;?>");
					}
					else{
						alert(data);
					}
				});
			}
		</script>
		<?php makeNav();?>
		<div class="container-fluid">
			<div class="col-sm-8 col-sm-offset-4">
				<form class="navbar-form navbar-left">
					<div class="form-group">
						<input id="email" type="text" class="form-control" placeholder="Email Address">
						<input id="unameSignup" type="text" class="form-control" placeholder="Username">
						<input id="passwdSignup" type="password" class="form-control" placeholder="Password">
						<a class="btn btn-success" style="color:#FFFFFF" href="#" onclick="signup();">Sign Up</a>
					</div>
				</form>
			</div>
		</div>
	</body>
</html>
