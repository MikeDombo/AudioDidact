<?php
spl_autoload_register(function($class){
	require_once __DIR__.'/classes/User.php';
});
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
require_once("views".DIRECTORY_SEPARATOR."views.php");
?>
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