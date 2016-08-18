<?php
require_once __DIR__."/header.php";

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
