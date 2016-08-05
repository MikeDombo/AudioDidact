<?php
require_once __DIR__.'/config.php';
spl_autoload_register(function($class){
	require_once __DIR__.'/classes/User.php';
});

if (session_status() == PHP_SESSION_NONE) {
	session_set_cookie_params(
		2678400,
		"/",
		parse_url(LOCAL_URL)["host"],
		false, //HTTPS only
		true
	);
	session_start();
}
setcookie(session_name(),session_id(),time()+2678400, "/", session_get_cookie_params()["domain"], false, true);

require_once("views".DIRECTORY_SEPARATOR."views.php");
?>
<!DOCTYPE html>
<html>
	<?php if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){makeHeader("Add a Video");}
	else{
		makeHeader("Home");
	}
	?>
	<body>
		<?php makeNav();?>
		<div class="container-fluid">
			<?php if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){makeAddVideo($_SESSION["user"]);}?>
		</div>
	</body>
</html>