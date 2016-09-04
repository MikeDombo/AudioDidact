<?php
require_once __DIR__."/header.php";
require_once(__DIR__."/views/views.php");
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
			<?php if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
				makeAddVideo();
				echo "</div>
					</body>
				</html>";
				exit(0);
			}?>
			
		</div>
	</body>
</html>
