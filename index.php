<?php
require_once __DIR__."/header.php";
require_once(__DIR__."/views/views.php");
?>

<!DOCTYPE html>
<html>
	<?php 
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
		makeHeader("Add a Video");
	}
	else{
		makeHeader("Home");
	}
	?>
	<body>
		<?php makeNav();?>
		<div id="main-content" class="container-fluid">
			<?php if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
				makeAddVideo();
				echo "</div>
					</body>
				</html>";
				exit(0);
			}?>
			<div class="jumbotron text-center">
				<h1 class="display-3">PodTube - YouTube to Podcast Service</h1>
				<p class="lead">Make an account and add videos to your feed today!</p>
			</div>
			<div class="col-md-4">
				<h3>What is PodTube?</h3>
				<hr/>
				<p>PodTube means taking YouTube on the go and sharing your favorite videos with friends.</p>
			</div>
			<div class="col-md-4">
				<h3>How Does This Work?</h3>
				<hr/>
				<h4><a href="signup.php">Sign up for an account</a> to get started.</h4>
				<p>Enter any YouTube video URL or video ID in the textbox and click "Add Video To Feed" to put the
				video into the created podcast feed. Then subscribe to the feed URL shown on the "Add Video" page
				with any podcatcher.</p>
			</div>
			<div class="col-md-4">
				<h3>Development</h3>
				<hr/>
				<p>PodTube is being developed by <a href="http://mikedombrowski.com" target="_blank">Michael Dombrowski</a>
				 and the source is available on my <a href="http://github.com/md100play/PodTube" target="_blank">GitHub</a>.
				 Documentation is online <a href="http://md100play.github.io/PodTube" target="_blank">here</a>.</p>
			</div>
		</div>
	</body>
</html>
