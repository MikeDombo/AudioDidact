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
				<h1 class="display-1">PodTube - YouTube to Podcast Service</h1>
				<h2>Make an account and add videos to your feed today!</h2>
			</div>
			<div class="row">
				<div class="col-md-4">
					<h1>What is PodTube?</h1>
					<hr/>
					<p>PodTube means taking YouTube on the go and sharing your favorite videos with friends.</p>
					<h4>PodTube does not work for playlists or channels, it is for adding individual videos only.</h4>
				</div>
				<div class="col-md-4">
					<h1>How Does This Work?</h1>
					<hr/>
					<h3><a href="signup.php">Sign up for an account</a> to get started.</h3>
					<p>Enter any YouTube video URL or video ID in the textbox and click "Add Video To Feed" to put the
					video into the created podcast feed. Then subscribe to the feed URL shown on the "Add Video" page
					with any podcatcher. PodTube does not work for playlists or channels, it is for adding individual videos only.</p>
				</div>
				<div class="col-md-4">
					<h1>Development</h1>
					<hr/>
					<p>PodTube is being developed by <a href="http://mikedombrowski.com" target="_blank">Michael Dombrowski</a>
					 and the source is available on my <a href="http://github.com/md100play/PodTube" target="_blank">GitHub</a>.
					 Documentation is online <a href="http://md100play.github.io/PodTube" target="_blank">here</a>.</p>
				</div>
			</div>
		</div>
	</body>
</html>
