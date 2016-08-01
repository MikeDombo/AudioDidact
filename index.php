<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
require_once("views".DIRECTORY_SEPARATOR."views.php");
?>
<html>
	
	<?php makeHeader("Sign Up");?>

	<body>
		<?php makeNav();?>
		<div class="container-fluid">
			<div class="col-sm-12">
				
			</div>
		</div>
	</body>
</html>