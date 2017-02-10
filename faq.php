<?php
require_once __DIR__."/header.php";
require_once(__DIR__."/views/views.php");
?>

<!DOCTYPE html>
<html>
	<?php
	makeHeader("FAQ");
	?>
	<body>
	<?php makeNav();?>
		<div id="main-content" class="container-fluid">
			<div class="row justify-content-center">
				<div class="col-lg-4">
					<div class="card mb-1">
						<h3 class="card-header">How do I access my feed?</h3>
						<div class="card-block">
							<p class="card-text">Your personal feed is located at <?php echo LOCAL_URL;?>user/<?php if(!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"]){echo "username";} else{echo $_SESSION["user"]->getWebID();}?>/feed</p>
							<p class="card-text">Enter that URL into your podcast player of choice. I like <a href="http://www.shiftyjelly.com/pocketcasts/">PocketCasts</a> for Android and iOS, or <a href="https://overcast.fm/">Overcast</a> for iOS.
								However, there are many other options as well and your personal feed will work in any of them.</p>
						</div>
					</div>
					<div class="card mb-1">
						<h3 class="card-header">Why the name <em>AudioDidact?</em></h3>
						<div class="card-block">
							<p class="card-text">It comes from the word autodidact.</p>
							<p class="blockquote"><strong>au·to·di·dact</strong> /ˌɔ toʊˈdaɪ dækt/ <br/>Noun - A self-taught person</p>
							<p class="card-text">I created AudioDidact to help me learn from the best thinkers online today,
							and from history. It started with only supporting YouTube videos, which I used to download TED
							Talks, debates, and lectures. I then added support for other websites that publish long-form
							content which I wanted to have as a podcast that I could playback faster.</p>
						</div>
					</div>
					<div class="card mb-1">
						<h3 class="card-header">What sites does AudioDidact support?</h3>
						<div class="card-block">
							<p class="card-text">AudioDidact currently supports the following services:</p>
							<ul class="list-group">
								<li class="list-group-item"><a target="_blank" href="https://youtube.com">YouTube</a></li>
								<li class="list-group-item"><a target="_blank" href="https://soundcloud.com">SoundCloud</a></li>
								<li class="list-group-item"><a target="_blank" href="https://crtv.com">CRTV</a></li>
							</ul>
							<p class="card-text pt-3">AudioDidact does not support playlists or channels, it is for adding
								individual videos and audio only.
							</p>
						</div>
					</div>
					<div class="card mb-1">
						<h3 class="card-header">How can I modify my feed settings?</h3>
						<div class="card-block">
							<p class="card-text">
								Your feed settings can be changed on your user account page. The settings
								that you can change include: podcast title, podcast icon, podcast author, podcast description,
								 number of items in feed, and privacy of your feed.
							</p>
						</div>
					</div>
					<div class="card mb-1">
						<h3 class="card-header">I don't want everyone to see what I'm listening to, can I keep
						my feed private?</h3>
						<div class="card-block">
							<p class="card-text">Your feed can absolutely be private. Simply click on the switch on your
							account page labeled "Feed is Private", which is off by default. When your feed is private
							no one else will be able to look at your feed, although if you have entered your name and
							gender, these will be shown. If you make your feed private you will have to provide your
							username and password to your podcast player so that your feed can be checked. Support for
							this does depend on your podcast player.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
