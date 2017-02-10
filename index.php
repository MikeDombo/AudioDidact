<?php
require_once __DIR__."/header.php";
require_once(__DIR__."/views/views.php");
?>

<!DOCTYPE html>
<html>
	<?php
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
		makeHeader("Add Content");
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
				<h1 class="display-1">AudioDidact - Custom Podcast Service</h1>
				<h2><a href="signup.php">Make an Account and Add Content to Your Feed Now!</a></h2>
			</div>
			<div class="row">
				<div class="col-md-4">
					<h1>What is AudioDidact?</h1>
					<hr/>
					<p>AudioDidact means taking the best content from the web on the go in an audio form.</p>
					<h4>AudioDidact does not work for playlists or channels, it is for adding individual videos/audio only.</h4>
				</div>
				<div class="col-md-4">
					<h1>How Does This Work?</h1>
					<hr/>
					<h3><a href="signup.php">Sign up for an account</a> to get started.</h3>
					<p>Enter any YouTube video or SoundCloud audio URL in the textbox and click "Add Video To Feed" to put the
					video into your custom podcast feed. Look at the <a href="faq.php">FAQ page</a> to see an updated
					list of supported websites. Next, subscribe to the feed URL shown on the "Add Video" page
					with any podcast player.</p>
				</div>
				<div class="col-md-4">
					<h1>Development</h1>
					<hr/>
					<p>AudioDidact is in active development by <a href="http://mikedombrowski.com" target="_blank">Michael Dombrowski</a>
					 and the source is available on my <a href="http://github.com/md100play/AudioDidact" target="_blank">GitHub</a>.
					 API Documentation is online <a href="http://md100play.github.io/AudioDidact" target="_blank">here</a>.</p>
				</div>
			</div>
		</div>
	</body>
</html>
