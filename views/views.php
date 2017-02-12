<?php
require_once __DIR__."/../header.php";

/**
 * Makes the global header with a given title.
 * @param $title
 */
function makeHeader($title){
		echo '<head>
				<meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
			    <style>
					body{
						word-wrap:break-word;
					}
				</style>
			    <script>
			    if(location.hostname != "localhost" && location.hostname != "127.0.0.1"){
			        (function(){
				        var t,i,e,n=window,o=document,a=arguments,s="script",r=["config","track","identify","visit","push","call","trackForm","trackClick"],c=function(){var t,i=this;for(i._e=[],t=0;r.length>t;t++)(function(t){i[t]=function(){return i._e.push([t].concat(Array.prototype.slice.call(arguments,0))),i}})(r[t])};for(n._w=n._w||{},t=0;a.length>t;t++)n._w[a[t]]=n[a[t]]=n[a[t]]||new c;i=o.createElement(s),i.async=1,i.src="//static.woopra.com/js/w.js",e=o.getElementsByTagName(s)[0],e.parentNode.insertBefore(i,e)
						})("woopra");
					woopra.config({
					    domain: \'ytpod.mikedombrowski.com\'
					});
					woopra.track();

				  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
				  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				  })(window,document,\'script\',\'https://www.google-analytics.com/analytics.js\',\'ga\');
				  ga(\'create\', \'UA-83794723-1\', \'auto\');
					ga(\'set\', {
					  dimension1: "';
						if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
							echo "true";
						}
						else{
							echo "false";
						}
					  echo '",
					});
					ga(\'send\', \'pageview\');
				}
				</script>
				<title>';
				if($title != ""){
					echo $title." | ";
				}
		echo 'AudioDidact</title>
			<style>
				#main-content {
					padding-top:1rem;
				}
			</style>
			<link rel="apple-touch-icon-precomposed" sizes="57x57" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-57x57.png" />
			<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-114x114.png" />
			<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-72x72.png" />
			<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-144x144.png" />
			<link rel="apple-touch-icon-precomposed" sizes="60x60" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-60x60.png" />
			<link rel="apple-touch-icon-precomposed" sizes="120x120" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-120x120.png" />
			<link rel="apple-touch-icon-precomposed" sizes="76x76" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-76x76.png" />
			<link rel="apple-touch-icon-precomposed" sizes="152x152" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-152x152.png" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-196x196.png" sizes="196x196" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-96x96.png" sizes="96x96" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-32x32.png" sizes="32x32" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-16x16.png" sizes="16x16" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-128.png" sizes="128x128" />
			<meta name="msapplication-TileColor" content="#FFFFFF" />
			<meta name="msapplication-TileImage" content="/'.SUBDIR.'public/img/favicon/mstile-144x144.png" />
			<meta name="msapplication-square70x70logo" content="/'.SUBDIR.'public/img/favicon/mstile-70x70.png" />
			<meta name="msapplication-square150x150logo" content="/'.SUBDIR.'public/img/favicon/mstile-150x150.png" />
			<meta name="msapplication-wide310x150logo" content="/'.SUBDIR.'public/img/favicon/mstile-310x150.png" />
			<meta name="msapplication-square310x310logo" content="/'.SUBDIR.'public/img/favicon/mstile-310x310.png" />
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
			<script src="/'.SUBDIR.'public/js/tether.min.js"></script>
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" crossorigin="anonymous">
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" crossorigin="anonymous"></script>

			<script>
				function validateLogin(){
					return $.trim($("#uname").val()) != "" && $.trim($("#passwd").val()) != "";
				}
				function login(){
					if(validateLogin()){
						$.post("/'.SUBDIR;
						echo 'login.php", {uname:$("#uname").val(), passwd:$("#passwd").val(),action:"login"},
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
					$.post("/'.SUBDIR;
					echo 'login.php", {action:"logout"}, function(data){
						console.log(data);
						if(data.indexOf("Success")>-1){
							location.reload();
						}
					});
				}
			</script>
		</head>';
	}

/**
 * Makes the navbar dynamically depending on the state of the current user
 */
function makeNav(){
		echo '
				<nav class="navbar navbar-toggleable-md navbar-inverse bg-inverse">
				  <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarDefault" aria-controls="navbarDefault" aria-expanded="false" aria-label="Toggle navigation">
			        <span class="navbar-toggler-icon"></span>
			      </button>
			    <a class="navbar-brand" href="/'.SUBDIR;echo '">AudioDidact</a>
			    <ul class="navbar-nav">
				    <li class="nav-item p-1">
				        <a class="nav-link" href="/'.SUBDIR; echo '">Home</a>
				    </li>
			    </ul>
				<div class="collapse navbar-collapse" id="navbarDefault">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item p-1">
							<a class="nav-link" href="/'.SUBDIR."faq.php"; echo '">FAQ</a>
						</li>
					</ul>
				  <ul class="navbar-nav">';
		if(!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"]){
			echo '
					<li class="nav-item dropdown p-1">
					  <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown" role="button"
					   aria-haspopup="true" aria-expanded="false">Login</a>
					  <div class="dropdown-menu" style="right:0; min-width:300px;left:initial;">
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
						<div class="p-2">
							<input id="uname" type="text" class="form-control" placeholder="Username">
							<input id="passwd" type="password" class="form-control" placeholder="Password">
						</div>
						<div class="dropdown-divider"></div>
						<div class="p-2">
							<a class="btn btn-success" style="color:#FFFFFF;width:100%;" href="#" onclick="login();">Login</a>
							<a href="/'.SUBDIR.'forgot" >Forgot Password</a>
						</div>
					  </div>
					</li>
					<li class="nav-item p-1"><a class="btn btn-success nav-link" href="/'.SUBDIR.'signup.php" 
					style="color:#FFFFFF;">Sign Up!</a></li>';
		}
		else{
			echo '
				<li class="nav-item p-1">
				  <a href="#" onclick="logout()" class="nav-link">Logout</a>
				</li>
				<li class="nav-item p-1"><a class="btn btn-success nav-link" 
				href="'.LOCAL_URL."user/"
				.$_SESSION["user"]->getWebID().'" style="color:#FFFFFF;">Account</a></li>';
		}
		echo '		</ul>
				</div>
			</nav>';
}

/**
 * Prints the add video webpage to the page along with the feed subscription url
 */
function makeAddVideo(){
	$loggedin = "false";
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
		$loggedin = "true";
	}
	$pug = new Pug\Pug(array('prettyprint' => true));
	$output = $pug->render('views/addVideo.pug', array(
		'subdir' => SUBDIR,
		'loggedIn' => $loggedin,
		'webID' => $_SESSION["user"]->getWebID(),
		'localurl' => LOCAL_URL
	));
	echo $output;
}

function makePasswordResetPage(User $user, $code){
	makeHeader("Reset Your Password");
	echo "<body>";
	makeNav();
?>
	<script>
		$(function(){
			$("#passwdSignup").keypress(function (e){
				if(e.which == 10 || e.which == 13){
					signup();
				}
			});
		});
		function signup(){
			$.get("/<?php echo SUBDIR;?>resetPassword", {uname:"<?php echo $user->getUsername();?>", passwd:$("#passwdSignup").val(), code:"<?php echo $code;?>"},
				function(data){
					if(data.indexOf("Success")>-1){
						location.assign("/<?php echo SUBDIR."user/"; echo $user->getWebID();?>");
					}
					else{
						alert(data);
					}
				});
		}
	</script>
	<div id="main-content" class="container-fluid">
		<div class="col-sm-8 offset-sm-2">
			<h3>Choose a New Password</h3>
			<form class="navbar-form navbar-left">
				<div class="form-group row">
					<label for="passwdSignup" class="col-form-label col-sm-2">Password:</label>
					<div class="col-sm-10">
						<input id="passwdSignup" type="password" class="form-control" placeholder="Password">
					</div>
				</div>
				<a class="btn btn-success form-control" style="color:#FFFFFF" href="#" onclick="signup();">Reset Password</a>
			</form>
		</div>
	</div>
<?php
	echo "</body></html>";
}

function makePasswordResetRequestPage(){
	makeHeader("Request a Password Reset");
	echo "<body>";
	makeNav();
	?>
	<script>
		$(function(){
			$("#unameSignup").keypress(function (e){
				if(e.which == 10 || e.which == 13){
					signup();
				}
			});
		});
		function signup(){
			$.get("/<?php echo SUBDIR;?>resetPassword", {uname:$("#unameSignup").val()}, function(data){
					alert(data);
					location.assign("/<?php echo SUBDIR;?>");
				});
		}
	</script>
	<div id="main-content" class="container-fluid">
		<div class="col-sm-8 offset-sm-2">
			<form class="navbar-form navbar-left">
				<div class="form-group row">
					<label for="unameSignup" class="col-form-label col-sm-2">Username or Email Address:</label>
					<div class="col-sm-10">
						<input id="unameSignup" type="text" class="form-control" placeholder="Username">
					</div>
				</div>
				<a class="btn btn-success form-control" style="color:#FFFFFF" href="#" onclick="signup();">Request a Password Reset</a>
			</form>
		</div>
	</div>
	<?php
	echo "</body></html>";
}
?>
