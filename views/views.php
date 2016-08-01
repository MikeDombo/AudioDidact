<?php
require_once __DIR__.'/../config.php';
spl_autoload_register(function($class){
	require_once __DIR__.'/../classes/User.php';
});
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

	function makeHeader($title){
		echo '<head>
			<title>YouTube to Podcast';
			if($title != ""){
				echo " | ".$title;
			}
		echo '</title>
			<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
			<script>
				function validateLogin(){
					if($.trim($("#uname").val()) != "" && $.trim($("#passwd").val()) != ""){
						return true;
					}
					return false;
				}
				function login(){
					if(validateLogin()){
						$.post("/podtube/login.php", {uname:$("#uname").val(), passwd:$("#passwd").val(), action:"login"}, 
						function
						(data){
							console.log(data);
							if(data.indexOf("Success")>-1){
								location.reload();
							}
							else{
								alert("Could not login with those credentials. Please make sure they are correct.");
							}
						});
					}
					else{
						alert("Could not validate login information, please check username and password");
					}
				}
				function logout(){
					$.post("/podtube/login.php", {action:"logout"}, function(data){
						console.log(data);
						if(data.indexOf("Success")>-1){
							location.reload();
						}
					});
				}
			</script>
		</head>';
	}
	
	function makeNav(){
		echo '<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
				  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				  </button>
				  <a class="navbar-brand" href="index.php">PodTube</a>
				</div>
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				  <ul class="nav navbar-nav">
					<li class="active"><a href="index.php">Home</a></li>
				  </ul>
				  <ul class="nav navbar-nav navbar-right">';
		if(!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"]){
			echo '
					<li class="dropdown">
					  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Login <span class="caret"></span></a>
					  <ul class="dropdown-menu">
						<form class="navbar-form navbar-left">
							<script>
								$(function() {
									$("#uname").keypress(function(e) {
										if(e.which == 10 || e.which == 13) {
											login();
										}
									});
									$("#passwd").keypress(function(e) {
										if(e.which == 10 || e.which == 13) {
											login();
										}
									});
								});
							</script>
							<div class="form-group">
								<input id="uname" type="text" class="form-control" placeholder="Username">
								<input id="passwd" type="password" class="form-control" placeholder="Password">
							</div>
						</form>
						<li role="separator" class="divider"></li>
						<li><a class="btn btn-success" style="color:#FFFFFF" href="#" onclick="login();">Login</a></li>
					  </ul>
					</li>
					<li><a class="btn btn-success" href="signup.php" style="color:#FFFFFF;">Sign Up!</a></li>';
		}
		else{
			echo '
				<li>
				  <a href="#" onclick="logout()">Logout</a>
				</li>
				<li><a class="btn btn-success" href="#" style="color:#FFFFFF;">Account</a></li>	
			';
		}
		echo '		</ul>
				</div>
			</div>
		</nav>';
}

function makeAddVideo(){
	echo file_get_contents(__DIR__.DIRECTORY_SEPARATOR."addVideoView.html");
	$feedURL = LOCAL_URL."user/".$_SESSION["user"]->getWebID()."/feed/";
	echo "<h2>Feed Subscription URL: <a href='$feedURL'>$feedURL</a></h2>";
}
?>