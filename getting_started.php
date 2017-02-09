<?php
require_once __DIR__."/header.php";
require_once(__DIR__."/views/views.php");
?>

<!DOCTYPE html>
<html>
	<?php
		makeHeader("Getting Started with AudioDidact");
	?>
	<body>
		<?php makeNav();?>
		<div id="main-content" class="container-fluid">
			<div class="row">
				<div class="col-sm-4">
					<div class="card">
						<div class="card-block">
							<h4 class="card-title">What <em>Exactly</em> Does AudioDidact Do?</h4>
							<p class="card-text">AudioDidact generate an RSS feed that can be subscribed to like any podcast. You put videos into the podcast feed by entering in YouTube URLs on the homepage.</p>
							<p class="card-text">AudioDidact only accepts individual videos, not channels or playlists.</p>
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="card">
						<div class="card-block">
							<h4 class="card-title">How Do I Access My Feed?</h4>
							<p class="card-text">Your personal feed is located at <?php echo LOCAL_URL;?>user/<?php if(!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"]){echo "username";} else{echo $_SESSION["user"]->getWebID();}?>/feed</p>
							<p class="card-text">Enter that URL into your podcast player of choice, I like <a href="http://www.shiftyjelly.com/pocketcasts/">PocketCasts</a> for Android and iOS, or <a href="https://overcast.fm/">Overcast</a> for iOS. 
							However, there are many other options as well and your personal feed will work in any of them.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>