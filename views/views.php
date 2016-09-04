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
			    <meta http-equiv="x-ua-compatible" content="ie=edge">
				<title>YouTube to Podcast';
				if($title != ""){
					echo " | ".$title;
				}
		echo '</title>
			<style>
				@import url("//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css");
				#main-content {
					padding-top:10px;
				}
			</style>
			<link rel="shortcut icon" href="/'.SUBDIR;
		echo 'favicon.ico" type="image/x-icon">
			<link rel="icon" href="/'.SUBDIR;
		echo 'favicon.ico" type="image/x-icon">
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.3/css/bootstrap.min.css" integrity="sha384-MIwDKRSSImVFAZCVLtU0LMDdON6KVCrZHyVQQj6e8wIEJkW4tvwqXrbMIya1vriY" crossorigin="anonymous">
			<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.2.0/js/tether.min.js" integrity="sha384-Plbmg8JY28KFelvJVai01l8WyZzrYWG825m+cZ0eDDS1f7d/js6ikvy1+X+guPIB" crossorigin="anonymous"></script>
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.3/js/bootstrap.min.js" integrity="sha384-ux8v3A6CPtOTqOzMKiuo3d/DomGaaClxFYdCu2HPMBEkf6x2xiDyJ7gkXU0MWwaD" crossorigin="anonymous"></script>
			
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
				<nav class="navbar navbar-dark bg-inverse navbar-full">
				  <button class="navbar-toggler hidden-sm-up pull-xs-right" type="button" data-toggle="collapse" 
				  data-target="#bs-example-navbar-collapse-1" aria-controls="bs-example-navbar-collapse-1" aria-expanded="false" aria-label="Toggle navigation">
				    &#9776;
				  </button>
			    <a class="navbar-brand" href="/'.SUBDIR;echo '">PodTube</a>
			    <ul class="nav navbar-nav">
				    <li class="nav-item">
				        <a class="nav-link" href="/'.SUBDIR; echo '">Home</a>
				    </li>
			    </ul>
				<div class="collapse navbar-toggleable-xs" id="bs-example-navbar-collapse-1">
				  <ul class="nav navbar-nav pull-xs-right">';
		if(!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"]){
			echo '
					<li class="dropdown nav-item">
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
						<div class="dropdown-item">
							<input id="uname" type="text" class="form-control" placeholder="Username">
							<input id="passwd" type="password" class="form-control" placeholder="Password">
						</div>
						<div class="dropdown-divider"></div>
						<div class="dropdown-item"><a class="btn btn-success" style="color:#FFFFFF;width:100%;" 
						href="#" onclick="login();">Login</a></div>
					  </div>
					</li>
					<li class="nav-item"><a class="btn btn-success nav-link" href="signup.php" 
					style="color:#FFFFFF;">Sign 
					Up!</a></li>';
		}
		else{
			echo '
				<li class="nav-item">
				  <a href="#" onclick="logout()" class="nav-link">Logout</a>
				</li>
				<li class="nav-item"><a class="btn btn-success nav-link" href="'.LOCAL_URL."user/".$_SESSION["user"]->getWebID().'" 
				style="color:#FFFFFF;">Account</a></li>	
			';
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
	echo "<div class='col-sm-12' style='word-wrap:break-word;'><h2>Feed Subscription URL: <a href='$feedURL'>$feedURL</a></h2></div>";
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
			echo '<div class="card-header"><h4>'.$i->getTitle().'</h4>';
			echo '<h5 class="text-muted">'.$i->getAuthor().'</h5></div><div class="card-block">';
			$descr = $i->getDesc();

			$words = explode("\n", $descr, 4);
			if(count($words)>3){
				$words[3] = "<p id='".$i->getId()."' style='display:none;'>".trim($words[3])."</p></p>";
				$words[4] = "<a onclick='$(\"#".$i->getId()."\").show();'>Continue Reading...</a>";
			}
			$descr = implode("\n", $words);

			$descr = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~\@]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $descr);
			$descr = nl2br($descr);
			echo '<div class="card-text"><p><img class="alignleft size-medium" src="'.LOCAL_URL.DOWNLOAD_PATH.'/'.$i->getId().'.jpg" width="300" 
			height="170" /></p>
			<p>'.$descr.'</div>';
			echo '</div></div>';
		}
		?>
	</div>
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
	showFeed($user);
}

/**
 * Makes the editable user profile page
 * @param User $user
 */
function makeEditProfile(User $user){
	?>
		<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
		<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
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
					<h2 class="modal-title">PodTube Error</h2>
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
