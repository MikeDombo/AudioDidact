<?php
require_once __DIR__."/../header.php";

/**
 * Makes the global header with a given title.
 * @param $title
 */
function makeHeader($title){
		echo '<head>
				<meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
			    <style>
					body{
						word-wrap:break-word;
					}
				</style>
			    <script>
			    if(location.hostname != "localhost" && location.hostname != "127.0.0.1"){
			        (function(){
				        var t,i,e,n=window,o=document,a=arguments,s="script",r=["config","track","identify","visit","push","call","trackForm","trackClick"],c=function(){var t,i=this;for(i._e=[],t=0;r.length>t;t++)(function(t){i[t]=function(){return i._e.push([t].concat(Array.prototype.slice.call(arguments,0))),i}})(r[t])};for(n._w=n._w||{},t=0;a.length>t;t++)n._w[a[t]]=n[a[t]]=n[a[t]]||new c;i=o.createElement(s),i.async=1,i.src="//static.woopra.com/js/w.js",e=o.getElementsByTagName(s)[0],e.parentNode.insertBefore(i,e)
						})("woopra");
					woopra.config({
					    domain: \'ytpod.mikedombrowski.com\'
					});
					woopra.track();

				  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
				  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				  })(window,document,\'script\',\'https://www.google-analytics.com/analytics.js\',\'ga\');
				  ga(\'create\', \'UA-83794723-1\', \'auto\');
					ga(\'set\', {
					  dimension1: "';
						if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]){
							echo "true";
						}
						else{
							echo "false";
						}
					  echo '",
					});
					ga(\'send\', \'pageview\');
				}
				</script>
				<title>AudioDidact';
				if($title != ""){
					echo " | ".$title;
				}
		echo '</title>
			<style>
				#main-content {
					padding-top:1rem;
				}
			</style>
			<link rel="apple-touch-icon-precomposed" sizes="57x57" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-57x57.png" />
			<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-114x114.png" />
			<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-72x72.png" />
			<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-144x144.png" />
			<link rel="apple-touch-icon-precomposed" sizes="60x60" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-60x60.png" />
			<link rel="apple-touch-icon-precomposed" sizes="120x120" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-120x120.png" />
			<link rel="apple-touch-icon-precomposed" sizes="76x76" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-76x76.png" />
			<link rel="apple-touch-icon-precomposed" sizes="152x152" href="/'.SUBDIR.'public/img/favicon/apple-touch-icon-152x152.png" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-196x196.png" sizes="196x196" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-96x96.png" sizes="96x96" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-32x32.png" sizes="32x32" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-16x16.png" sizes="16x16" />
			<link rel="icon" type="image/png" href="/'.SUBDIR.'public/img/favicon/favicon-128.png" sizes="128x128" />
			<meta name="msapplication-TileColor" content="#FFFFFF" />
			<meta name="msapplication-TileImage" content="/'.SUBDIR.'public/img/favicon/mstile-144x144.png" />
			<meta name="msapplication-square70x70logo" content="/'.SUBDIR.'public/img/favicon/mstile-70x70.png" />
			<meta name="msapplication-square150x150logo" content="/'.SUBDIR.'public/img/favicon/mstile-150x150.png" />
			<meta name="msapplication-wide310x150logo" content="/'.SUBDIR.'public/img/favicon/mstile-310x150.png" />
			<meta name="msapplication-square310x310logo" content="/'.SUBDIR.'public/img/favicon/mstile-310x310.png" />
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
			<script src="/'.SUBDIR.'public/js/tether.min.js"></script>
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" crossorigin="anonymous">
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" crossorigin="anonymous"></script>

			<script>
				function validateLogin(){
					return $.trim($("#uname").val()) != "" && $.trim($("#passwd").val()) != "";
				}
				function login(){
					if(validateLogin()){
						$.post("/'.SUBDIR;
						echo 'login.php", {uname:$("#uname").val(), passwd:$("#passwd").val(),action:"login"},
						function
						(data){
							console.log(data);
							if(data.indexOf("Success")>-1){
								location.reload();
							}
							else{
								alert("Could not login with those credentials. Please make sure they are correct.");
							}
						});
					}
					else{
						alert("Could not validate login information, please check username and password");
					}
				}
				function logout(){
					$.post("/'.SUBDIR;
					echo 'login.php", {action:"logout"}, function(data){
						console.log(data);
						if(data.indexOf("Success")>-1){
							location.reload();
						}
					});
				}
			</script>
		</head>';
	}

/**
 * Makes the navbar dynamically depending on the state of the current user
 */
function makeNav(){
		echo '
				<nav class="navbar navbar-toggleable-md navbar-inverse bg-inverse">
				  <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarDefault" aria-controls="navbarDefault" aria-expanded="false" aria-label="Toggle navigation">
			        <span class="navbar-toggler-icon"></span>
			      </button>
			    <a class="navbar-brand" href="/'.SUBDIR;echo '">AudioDidact</a>
			    <ul class="navbar-nav">
				    <li class="nav-item p-1">
				        <a class="nav-link" href="/'.SUBDIR; echo '">Home</a>
				    </li>
			    </ul>
				<div class="collapse navbar-collapse" id="navbarDefault">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item p-1">
							<a class="nav-link" href="/'.SUBDIR."getting_started.php"; echo '">Getting Started</a>
						</li>
						<li class="nav-item p-1">
							<a class="nav-link" href="/'.SUBDIR."faq.php"; echo '">FAQ</a>
						</li>
					</ul>
				  <ul class="navbar-nav">';
		if(!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"]){
			echo '
					<li class="nav-item dropdown p-1">
					  <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown" role="button"
					   aria-haspopup="true" aria-expanded="false">Login</a>
					  <div class="dropdown-menu" style="right:0; min-width:300px;left:initial;">
						<script>
							$(function() {
								$("#uname").keypress(function(e) {
									if(e.which == 10 || e.which == 13) {
										login();
									}
								});
								$("#passwd").keypress(function(e) {
									if(e.which == 10 || e.which == 13) {
										login();
									}
								});
							});
						</script>
						<div class="p-2">
							<input id="uname" type="text" class="form-control" placeholder="Username">
							<input id="passwd" type="password" class="form-control" placeholder="Password">
						</div>
						<div class="dropdown-divider"></div>
						<div class="p-2"><a class="btn btn-success" style="color:#FFFFFF;width:100%;" href="#" 
						onclick="login();">Login</a>
						</div>
					  </div>
					</li>
					<li class="nav-item p-1"><a class="btn btn-success nav-link" href="signup.php" 
					style="color:#FFFFFF;">Sign Up!</a></li>';
		}
		else{
			echo '
				<li class="nav-item p-1">
				  <a href="#" onclick="logout()" class="nav-link">Logout</a>
				</li>
				<li class="nav-item p-1"><a class="btn btn-success nav-link" 
				href="'.LOCAL_URL."user/"
				.$_SESSION["user"]->getWebID().'" style="color:#FFFFFF;">Account</a></li>';
		}
		echo '		</ul>
				</div>
			</nav>';
}

/**
 * Prints the add video webpage to the page along with the feed subscription url
 */
function makeAddVideo(){
	echo file_get_contents(__DIR__.DIRECTORY_SEPARATOR."addVideoView.html");
	$feedURL = LOCAL_URL."user/".$_SESSION["user"]->getWebID()."/feed/";
	echo "<div class='col-sm-12'><h2>Feed Subscription URL: <a href='$feedURL'>$feedURL</a></h2></div>";
}

/**
 * Prints all feed items as HTML
 * @param User $user
 */
function showFeed(User $user){
	?>
	<hr/>
	<div class="col-md-6">
		<h2>Feed Items</h2>
		<?php
		$myDalClass = ChosenDAL;
		$dal = new $myDalClass(DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);

		$items = $dal->getFeed($user);
		for($x=0;$x<$user->getFeedLength() && isset($items[$x]);$x++){
			$i = $items[$x];
			echo '<div class="card">';
			echo '<div class="card-block">';
			echo '<h4 class="card-title">'.$i->getTitle().'</h4>';
			echo '<h5 class="card-title text-muted">'.$i->getAuthor().'</h5>';
			$descr = $i->getDesc();

			$words = explode("\n", $descr, 4);
			if(count($words)>3){
				$words[3] = "<p id='".$i->getId()."' style='display:none;'>".trim($words[3])." </p></p>";
				$words[4] = "<a onclick='$(\"#".$i->getId()."\").show();'>Continue Reading...</a>";
			}
			$descr = implode("\n", $words);

			$descr = mb_ereg_replace('(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~\@!]*(\?\S+)?)?)?)', '<a href="\\1">\\1</a>', $descr);
			$descr = nl2br($descr);
			echo '<img class="img-fluid img-thumbnail" src="'.LOCAL_URL.DOWNLOAD_PATH.'/'.$i->getId().'.jpg" style="max-height:300px;" />';
			echo '<audio controls style="width:100%" class="m-1" preload="none">
				     <source src="'.LOCAL_URL.DOWNLOAD_PATH.'/'.$i->getId().'.mp3" type="audio/mpeg">
				     Your browser does not support the audio element.
				  </audio>';
			echo '<button class="btn btn-outline-info btn-large playspeed">Playback Speed: x</button>';
			echo '<div class="card-text"><p>'.$descr.'</div>';
			echo '</div></div>';
		}
		?>
	</div>
	<script>
		$(document).ready(function(){
			$(".playspeed").each(function(){
				var pb = $(this).parent().find("audio").get(0).playbackRate;
				$(this).text("Playback Speed: "+pb+"x");
			});
		});
		$(".playspeed").on("click", function(){
			var audioElement = $(this).parent().find("audio").get(0);
			var originalSpeed = audioElement.playbackRate;
			var newSpeed = 0;
			if(originalSpeed < 3){
				newSpeed = originalSpeed + .5;
			}
			else{
				newSpeed = 0.5;
			}
			audioElement.playbackRate = newSpeed;
			$(this).text("Playback Speed: "+newSpeed+"x");
		});
		$("audio").on("play", function(){
			var _this = $(this);
			$("audio").each(function(i,el){
				if(!$(el).is(_this))
					$(el).get(0).pause();
			});
		});
	</script>
	<?php
}

/**
 * Makes the user profile view for the general public
 * @param User $user
 */
function makeViewProfile(User $user){
	?>
	<div class="col-sm-12">
		<h1><?php echo $user->getWebID();?>'s Profile</h1>
		<hr/>

		<div>
			<label for="profileURL">Profile URL:</label><a id="profileURL" href="<?php echo LOCAL_URL."user/".$user->getWebID();?>">
			<?php echo LOCAL_URL."user/".$user->getWebID()?>
			</a>
		</div>
		<div>
			<label for="feedURL">Feed URL:</label><a id="feedURL" href="<?php echo LOCAL_URL."user/".$user->getWebID()."/feed";?>">
			<?php echo LOCAL_URL."user/".$user->getWebID()."/feed";?>
			</a>
		</div>
		<div>
			<label for="name">Full Name:</label><span id="name"></span><?php echo $user->getFname()." ".$user->getLname();?></span>
		</div>
		<div>
			<label for="gender">Gender:</label>
			<span id="gender"><?php $g=$user->getGender(); if($g == 1){echo "Male";}else if($g == 2){echo "Female";}else if($g == 3){echo "Other";}?>
			</span>
		</div>
	</div>
	<?php
	if(!$user->isPrivateFeed()){
		showFeed($user);
	}
	else{
		echo "<h4>User's feed is private</h4>";
	}
}

/**
 * Makes the editable user profile page
 * @param User $user
 */
function makeEditProfile(User $user){
	?>
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
		<link href="/<?php echo SUBDIR;?>public/css/bootstrap-editable.min.css" rel="stylesheet"/>
		<script src="/<?php echo SUBDIR;?>public/js/bootstrap-editable.min.js"></script>
		<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
		<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
		<div class="col-sm-12">
			<h1><?php echo $user->getUsername();?>'s Profile</h1>
			<hr/>

			<div class="col-md-6">
				<div class="form-group">
					<label for="webID">Custom URL:</label>
					<span><?php echo LOCAL_URL?>user/</span><a href="#" id="webID" data-type="text" data-pk="1" data-url="/<?php echo SUBDIR;?>
					updateUser.php" data-title="Enter Custom URL"><?php echo $user->getWebID();?></a><span>/</span>
				</div>
				<div class="form-group">
					<label for="fname">Firstname:</label>
					<a href="#" id="fname" data-type="text" data-pk="1" data-url="/<?php echo SUBDIR;?>updateUser.php" data-title="Enter Firstname"><?php echo $user->getFname();?></a>
					<label for="lname">Lastname:</label>
					<a href="#" id="lname" data-type="text" data-pk="1" data-url="/<?php echo SUBDIR;?>updateUser.php" data-title="Enter Lastname"><?php echo $user->getLname();?></a>
				</div>
				<div class="form-group">
					<label for="gender">Gender:</label>
					<a href="#" id="gender" data-type="select" data-pk="1" data-url="/<?php echo SUBDIR;?>updateUser.php" data-title="Select Gender"></a>
				</div>
				<div class="form-group">
					<label for="email">Email:</label>
					<a href="#" id="email" data-type="text" data-pk="1" data-url="/<?php echo SUBDIR;?>updateUser.php" data-title="Enter Email"><?php echo $user->getEmail();?></a>
				</div>
				<div class="form-group">
					<label for="feedLen">Number of Items in Feed:</label>
					<a href="#" id="feedLen" data-type="number" data-pk="1" data-url="/<?php echo SUBDIR;?>updateUser.php" data-title="Enter # of Items in Feed (Max 50)" data-min="1" data-max="50"><?php echo $user->getFeedLength();?></a>
				</div>
				<div class="form-group">
					<label for="privateFeed">Feed is Private:</label>
					<input id="privateFeed" type="checkbox" <?php if($user->isPrivateFeed()){echo "checked";}?> data-toggle="toggle">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<img id="feedIcoImg" style="max-height: 150px; max-width: 150px;"/>
				</div>
				<div class="form-group">
					<label for="feedTitle">Feed Title:</label>
					<a href="#" id="feedTitle" data-type="text" data-pk="1" data-url="/<?php echo SUBDIR;?>updateUser.php">
						<?php echo $user->getFeedDetails()["title"];?>
					</a>
				</div>
				<div class="form-group">
					<label for="feedDesc">Feed Description:</label>
					<a href="#" id="feedDesc" data-type="textarea" data-pk="1" data-url="/<?php echo SUBDIR;?>updateUser
					.php"><?php echo htmlentities($user->getFeedDetails()["description"]);?></a>
				</div>
				<div class="form-group">
					<label for="feedIco">Feed Icon:</label>
					<a href="#" id="feedIco" data-type="text" data-pk="1" data-url="/<?php echo SUBDIR;?>
					updateUser.php" data-tpl="<input type='text' size='100' style='width:100%;'>"><?php echo
						$user->getFeedDetails()["icon"];?></a>
				</div>
				<div class="form-group">
					<label for="itunesAuthor">iTunes Author:</label>
					<a href="#" id="itunesAuthor" data-type="text" data-pk="1" data-url="/<?php echo SUBDIR;?>
					updateUser.php"><?php echo $user->getFeedDetails()["itunesAuthor"];?></a>
				</div>
			</div>
		</div>

		<?php showFeed($user);?>

		<script>
			function processSuccess(response){
				var message = JSON.parse(response);
				if(!message.success){
					return message.error;
				}
			}
			function processError(response){
				$('#Error-Modal-Text').text(response);
				$('#Error-Modal').modal({
					show: true
				});
			}
			$(function() {
				$('#privateFeed').bootstrapToggle();
				$('#privateFeed').change(function(){
					$.post("/<?php echo SUBDIR;?>updateUser.php", {name:"privateFeed", value:$(this).prop('checked')
					},function(data){
						if(processSuccess(data)){
							processError(processSuccess(data));
						}
					});
				});
			});
			$.fn.editable.defaults.mode = 'inline';
			$(document).ready(function() {
				$("#feedIcoImg").attr("src", $("#feedIco").text());
				var basicOptions = {
					success: function(response, newValue){console.log(response);console.log(newValue);
						return processSuccess(response);},
					error: function(response){console.log(response);return processError(response);}
				};
				var feedOptionArray = {
					success: function(response, newValue){
						var message = JSON.parse(response);
						if(!message.success){
							return message.error;
						}
						$.get( "/<?php echo SUBDIR;?>yt.php", function( data ) {});
					},
					error: function(response){console.log(response);return processError(response);}
				};

				$('#feedTitle').editable(feedOptionArray);
				$('#itunesAuthor').editable(feedOptionArray);
				$('#feedDesc').editable(feedOptionArray);
				$('#feedIco').editable({
					success: function(response, newValue){
						var message = JSON.parse(response);
						if(!message.success){
							return message.error;
						}
						$("#feedIcoImg").attr("src", newValue);
						$.get( "/<?php echo SUBDIR;?>yt.php", function( data ) {});
					},
					error: function(response){console.log(response);return processError(response);}
				});

				$('#webID').editable({
					success: function(response, newValue){
						var message = JSON.parse(response);
						if(!message.success){
							return message.error;
						}
						else{
							window.location = "<?php echo "/".SUBDIR;?>"+"user/"+newValue;
						}
					},
					error: function(response){console.log(response);return processError(response);}
				});
				$('#fname').editable(basicOptions);
				$('#lname').editable(basicOptions);
				$('#email').editable(basicOptions);
				$('#feedLen').editable({
					success: function(response, newValue){
						var message = JSON.parse(response);
						if(!message.success){
							return message.error;
						}
						$.get( "/<?php echo SUBDIR;?>yt.php", function( data ) {});
					},
					error: function(response){console.log(response);return processError(response);}
				});
				$('#gender').editable({
					value: <?php echo $user->getGender();?>,
					source: [
						{value: 1, text: 'Male'},
						{value: 2, text: 'Female'},
						{value: 3, text: 'Other'}
					],
					success: function(response){console.log(response);return processSuccess(response);},
					error: function(response){console.log(response);return processError(response);}
				});
			});
		</script>

		<div id="Error-Modal" class="modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h2 class="modal-title">AudioDidact Error</h2>
					<div class="modal-body">
						<h3 id="Error-Modal-Text"></h3>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
}
?>
