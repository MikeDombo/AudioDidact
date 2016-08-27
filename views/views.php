<?php
require_once __DIR__."/../header.php";

/**
 * Makes the global header with a given title.
 * @param $title
 */
function makeHeader($title){
		echo '<head>
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>YouTube to Podcast';
				if($title != ""){
					echo " | ".$title;
				}
		echo '</title>
			<link rel="shortcut icon" href="/'.SUBDIR;
		echo 'favicon.ico" type="image/x-icon">
			<link rel="icon" href="/'.SUBDIR;
		echo 'favicon.ico" type="image/x-icon">
			<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
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
		echo '<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
				  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				  </button>
				  <a class="navbar-brand" href="/'.SUBDIR;echo '">PodTube</a>
				</div>
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				  <ul class="nav navbar-nav">
					<li class="active"><a href="/'.SUBDIR;
				echo '">Home</a></li>
				  </ul>
				  <ul class="nav navbar-nav navbar-right">';
		if(!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"]){
			echo '
					<li class="dropdown">
					  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Login <span class="caret"></span></a>
					  <ul class="dropdown-menu">
						<form class="navbar-form navbar-left">
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
							<div class="form-group">
								<input id="uname" type="text" class="form-control" placeholder="Username">
								<input id="passwd" type="password" class="form-control" placeholder="Password">
							</div>
						</form>
						<li role="separator" class="divider"></li>
						<li><a class="btn btn-success" style="color:#FFFFFF" href="#" onclick="login();">Login</a></li>
					  </ul>
					</li>
					<li><a class="btn btn-success" href="signup.php" style="color:#FFFFFF;">Sign Up!</a></li>';
		}
		else{
			echo '
				<li>
				  <a href="#" onclick="logout()">Logout</a>
				</li>
				<li><a class="btn btn-success" href="'.LOCAL_URL."user/".$_SESSION["user"]->getWebID().'" style="color:#FFFFFF;">Account</a></li>	
			';
		}
		echo '		</ul>
				</div>
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
			echo '<div class="panel panel-default">';
			echo '<div class="panel-heading">'.$i->getTitle().' -- '.$i->getAuthor().'</div>';
			$descr = $i->getDesc();

			$words = explode("\n", $descr, 4);
			if(count($words)>3){
				$words[3] = "<p id='".$i->getId()."' class='hide'>".trim($words[3])."</p></p>";
				$words[4] = "<a onclick='$(\"#".$i->getId()."\").removeClass(\"hide\");'>Continue Reading...</a>";
			}
			$descr = implode("\n", $words);

			$descr = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#~\@]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $descr);
			$descr = nl2br($descr);
			echo '<div class="panel-body"><h3>'.$i->getAuthor().'</h3>
			<p><img class="alignleft size-medium" src="'.LOCAL_URL.DOWNLOAD_PATH.'/'.$i->getId().'.jpg" width="300" 
			height="170" /></p>
			<p>'.$descr.'</p></div>';
			echo '</div>';
		}
		?>
	</div>
	<?php
}

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
	<?php
	showFeed($user);
}

function makeEditProfile(User $user){
	?>
		<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
		<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
		<div class="col-sm-12">
		<h1><?php echo $user->getUsername();?>'s Profile</h1>
		<hr/>

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
				var basicOptions = {
					success: function(response, newValue){console.log(response);console.log(newValue);
						return processSuccess(response);},
					error: function(response){console.log(response);return processError(response);}
				};
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
				$('#feedLen').editable(basicOptions);
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
