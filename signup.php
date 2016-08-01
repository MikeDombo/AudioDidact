<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
if($_SERVER['REQUEST_METHOD'] == "PUT"){
	if(isset($_POST["uname"]) && isset($_POST["passwd"]) && isset($_POST["email"])){
		$_SESSION["username"] = $_POST["uname"];
		$_SESSION["email"] = $_POST["email"];
		
		// Check login info, set loggedIn to true if the information is correct
		$_SESSION["loggedIn"] = true;
		echo "Login Success!";
	}
	else{
		$_SESSION["loggedIn"] = false;
		echo "Login Failed!";
	}
}
require_once("views".DIRECTORY_SEPARATOR."views.php");
?>
<html>
	
	<?php makeHeader("Sign Up");?>
	<body>
		<script>
			function signup(){
				$.post("/podtube/signup.php", {uname:$("#uname").val(), passwd:$("#passwd").val(), email:$("#email").val()}, function(data){
					console.log(data);
					if(data.indexOf("Success")>-1){
						location.reload();
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
						<input id="uname" type="text" class="form-control" placeholder="Username">
						<input id="passwd" type="password" class="form-control" placeholder="Password">
						<a class="btn btn-success" style="color:#FFFFFF" href="#" onclick="signup();">Sign Up</a>
					</div>
				</form>
			</div>
		</div>
	</body>
</html>