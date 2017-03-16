<?php


/**
 * Class MySQLDAL contains methods for communicating with a SQL database
 *
 */
class MySQLDAL extends DAL{
	/** @var string Database table for storing user information */
	protected $userTable = "users";
	/** @var string Database table for storing feed/video information */
	protected $feedTable = "feed";
	/** @var string SQL host */
	private $host;
	/** @var string SQL database name */
	private $db;
	/** @var string SQL database username */
	private $username;
	/** @var string SQL database password */
	private $password;

	/**
	 * Function to set the PDO. Used by SQLite
	 * @param \PDO $PDO
	 */
	protected function setPDO(\PDO $PDO){
		parent::$PDO = $PDO;
	}

	/**
	 * Function to get the PDO. Used by SQLite
	 * @return \PDO $PDO
	 */
	protected function getPDO(){
		return parent::$PDO;
	}

	/**
	 * MySQLDAL constructor.
	 * Sets up parent's PDO object using the parameters that are passed in.
	 *
	 * @param string The hostname/ip and port of the database
	 * @param string $db The database name
	 * @param string $username The username used to connect to the database
	 * @param string $password The password used to connect to the database
	 * @throws \PDOException Rethrows any PDO exceptions encountered when connecting to the database
	 */
	public function __construct($host, $db, $username, $password){
		$this->host = $host;
		$this->db = $db;
		$this->username = $username;
		$this->password = $password;

		if(parent::$PDO == null){
			try{
				parent::$PDO = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $username, $password);
				parent::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch(PDOException $e){
				echo 'ERROR: '.$e->getMessage();
				throw $e;
			}
		}
	}

	/**
	 * Gets the user by the database user id
	 * @param int $id
	 * @return null|User
	 * @throws \PDOException
	 */
	public function getUserByID($id){
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->userTable WHERE ID=:id");
			$p->bindValue(":id", $id, PDO::PARAM_INT);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) > 1){
				return null;
			}
			if(count($rows) == 0){
				return null;
			}
			$rows = $rows[0];

			return $this->setUser($rows);
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Makes a new user object from a database select command.
	 * @param $rows Database rows retrieved from another method
	 * @return User
	 */
	private function setUser($rows){
		$user = new User();
		$user->setUserID($rows["ID"]);
		$user->setUsername($rows["username"]);
		$user->setPasswdDB($rows["password"]);
		$user->setEmail($rows["email"]);
		$user->setFname($rows["firstname"]);
		$user->setLname($rows["lastname"]);
		$user->setGender($rows["gender"]);
		$user->setWebID($rows["webID"]);
		$user->setFeedText($rows["feedText"]);
		$user->setFeedLength($rows["feedLength"]);
		if($rows["feedDetails"] != ""){
			$user->setFeedDetails(json_decode($rows["feedDetails"], true));
		}
		$user->setPrivateFeed($rows["privateFeed"]);
		if($rows["emailVerificationCodes"] != ""){
			$user->setEmailVerificationCodes(json_decode($rows["emailVerificationCodes"], true));
		}
		if($rows["passwordRecoveryCodes"] != ""){
			$user->setPasswordRecoveryCodes(json_decode($rows["passwordRecoveryCodes"], true));
		}
		$user->setEmailVerified($rows["emailVerified"]);

		return $user;
	}

	/**
	 * Gets a User object from a webID
	 * @param string $webID
	 * @return null|User
	 * @throws \PDOException
	 */
	public function getUserByWebID($webID){
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->userTable WHERE webID=:id");
			$p->bindValue(":id", $webID, PDO::PARAM_STR);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) > 1){
				return null;
			}
			if(count($rows) == 0){
				return null;
			}
			$rows = $rows[0];

			return $this->setUser($rows);
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Gets a User from the database based on a username
	 * @param string $username
	 * @return null|User
	 * @throws \PDOException
	 */
	public function getUserByUsername($username){
		$username = strtolower($username);
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->userTable WHERE username=:username");
			$p->bindValue(":username", $username, PDO::PARAM_STR);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) > 1){
				return null;
			}
			if(count($rows) == 0){
				return null;
			}
			$rows = $rows[0];

			return $this->setUser($rows);
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Gets User from an email
	 * @param string $email
	 * @return null|User
	 * @throws \PDOException
	 */
	public function getUserByEmail($email){
		$email = strtolower($email);
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->userTable WHERE email=:email");
			$p->bindValue(":email", $email, PDO::PARAM_STR);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) > 1){
				return null;
			}
			if(count($rows) == 0){
				return null;
			}
			$rows = $rows[0];

			return $this->setUser($rows);
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Gets Video for a User by the YouTube ID
	 * @param User $user
	 * @param $id
	 * @return null|Video
	 * @throws \PDOException
	 */
	public function getVideoByID(User $user, $id){
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->feedTable WHERE userID=:userid AND videoID=:videoID");
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_INT);
			$p->bindValue(":videoID", $id, PDO::PARAM_STR);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) < 1){
				return null;
			}
			$row = $rows[0];

			$vid = new Video();
			$vid->setAuthor($row["videoAuthor"]);
			$vid->setDesc($row["description"]);
			$vid->setId($row["videoID"]);
			$vid->setTime(strtotime($row["timeAdded"]));
			$vid->setTitle($row["videoTitle"]);
			$vid->setOrder($row["orderID"]);
			$vid->setURL($row["URL"]);
			return $vid;
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Adds a User object to the database
	 * @param User $user
	 * @throws \Exception|\PDOException
	 */
	public function addUser(User $user){
		if(!$this->usernameExists($user->getUsername()) && !$this->emailExists($user->getEmail())){
			try{
				$p = parent::$PDO->prepare("INSERT INTO $this->userTable (username, password, email, firstname,
				lastname, gender, webID, feedLength, feedText, feedDetails, privateFeed) VALUES (:username,:password,:email,
				:fname,:lname,:gender,:webID,:feedLength, :feedText,:feedDetails,:privateFeed)");
				$p->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
				$p->bindValue(':password', $user->getPasswd(), PDO::PARAM_STR);
				$p->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
				$p->bindValue(':fname', $user->getFname(), PDO::PARAM_STR);
				$p->bindValue(':lname', $user->getLname(), PDO::PARAM_STR);
				$p->bindValue(':gender', $user->getGender(), PDO::PARAM_INT);
				$p->bindValue(':webID', $user->getWebID(), PDO::PARAM_STR);
				$p->bindValue(':feedLength', $user->getFeedLength(), PDO::PARAM_INT);
				$p->bindValue(':feedText', $user->getFeedText(), PDO::PARAM_STR);
				$p->bindValue(':feedDetails', json_encode($user->getFeedDetails()), PDO::PARAM_STR);
				$p->bindValue(':privateFeed', $user->isPrivateFeed(), PDO::PARAM_BOOL);
				$p->execute();
			}
			catch(PDOException $e){
				echo 'ERROR: '.$e->getMessage();
				throw $e;
			}
		}
		else{
			throw new Exception("Username or Email Address Already Exists!");
		}
	}

	/**
	 * Adds a video to the feed table
	 * @param Video $vid
	 * @param User $user
	 * @return bool
	 * @throws \PDOException
	 */
	public function addVideo(Video $vid, User $user){
		try{
			// Find the largest orderID and add 1 to it to use as the orderID of the newest video
			$p = parent::$PDO->prepare("SELECT * FROM $this->feedTable WHERE userID=:userid ORDER BY orderID DESC LIMIT 1");
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_INT);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) == 0){
				$order = 1;
			}
			else{
				$order = intval($rows[0]["orderID"]) + 1;
			}

			// Add the new Video to the user's feed
			$p = parent::$PDO->prepare("INSERT INTO $this->feedTable (userID, URL, videoID, videoAuthor, description,
			videoTitle, duration, orderID, timeAdded) VALUES (:userID,:url,:videoID,:videoAuthor,:description,
			:videoTitle,:duration, :orderID,:time)");
			$p->bindValue(":userID", $user->getUserID(), PDO::PARAM_INT);
			$p->bindValue(":videoID", $vid->getId(), PDO::PARAM_STR);
			$p->bindValue(":url", $vid->getURL(), PDO::PARAM_STR);
			$p->bindValue(":videoAuthor", $vid->getAuthor(), PDO::PARAM_STR);
			$p->bindValue(":description", $vid->getDesc(), PDO::PARAM_STR);
			$p->bindValue(":videoTitle", $vid->getTitle(), PDO::PARAM_STR);
			$p->bindValue(":duration", $vid->getDuration(), PDO::PARAM_STR);
			$p->bindValue(":orderID", $order, PDO::PARAM_INT);
			$p->bindValue(":time", date("Y-m-d H:i:s", time()), PDO::PARAM_STR);
			$p->execute();

			return true;
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Gets full xml feed text from database
	 * @param User $user
	 * @return mixed
	 * @throws \PDOException
	 */
	public function getFeedText(User $user){
		try{
			$p = parent::$PDO->prepare("SELECT feedText FROM $this->userTable WHERE id=:userid");
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_INT);
			$p->execute();
			return $p->fetchAll(PDO::FETCH_ASSOC)[0]["feedText"];
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Returns an array of YouTube IDs that are in the feed
	 * @param User $user
	 * @return array|null
	 * @throws \PDOException
	 */
	public function getFeed(User $user){
		try{
			// Limit is not able to be a bound parameter, so I take the intval just to make sure nothing can get
			// injected
			$p = parent::$PDO->prepare("SELECT * FROM $this->feedTable WHERE userID=:userid ORDER BY orderID DESC LIMIT "
			.intval($user->getFeedLength()));
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_INT);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) < 1){
				return null;
			}
			$returner = [];
			foreach($rows as $row){
				$vid = new Video();
				$vid->setAuthor($row["videoAuthor"]);
				$vid->setDesc($row["description"]);
				$vid->setId($row["videoID"]);
				$vid->setTime(strtotime($row["timeAdded"]));
				$vid->setDuration(intval($row["duration"]));
				$vid->setTitle($row["videoTitle"]);
				$vid->setOrder($row["orderID"]);
				$vid->setURL($row["URL"]);
				$returner[] = $vid;
			}
			return $returner;
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Gets all the videos from the database
	 * @param User $user
	 * @return mixed
	 */
	public function getFullFeedHistory(User $user){
		try{
			// Limit is not able to be a bound parameter, so I take the intval just to make sure nothing can get
			// injected
			$p = parent::$PDO->prepare("SELECT * FROM $this->feedTable WHERE userID=:userid ORDER BY orderID DESC");
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_INT);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) < 1){
				return [];
			}
			$returner = [];
			foreach($rows as $row){
				$vid = new Video();
				$vid->setAuthor($row["videoAuthor"]);
				$vid->setDesc($row["description"]);
				$vid->setId($row["videoID"]);
				$vid->setTime(strtotime($row["timeAdded"]));
				$vid->setDuration(intval($row["duration"]));
				$vid->setTitle($row["videoTitle"]);
				$vid->setOrder($row["orderID"]);
				$vid->setURL($row["URL"]);
				$returner[] = $vid;
			}
			return $returner;
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Sets full feed text in the feed database
	 * @param User $user
	 * @param $feed
	 * @return bool
	 * @throws \PDOException
	 */
	public function setFeedText(User $user, $feed){
		try{
			$p = parent::$PDO->prepare("UPDATE $this->userTable set feedText=:feedText WHERE id=:userid");
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_INT);
			$p->bindValue(":feedText", $feed, PDO::PARAM_STR);
			$p->execute();
			return true;
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Checks if a video is in a user's feed
	 * @param Video $vid
	 * @param User $user
	 * @return bool
	 * @throws \PDOException
	 */
	public function inFeed(Video $vid, User $user){
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->feedTable WHERE userID=:userid AND videoID=:videoID");
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_INT);
			$p->bindValue(":videoID", $vid->getId(), PDO::PARAM_STR);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			return count($rows)>0;
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Updates only a user's password in the database
	 * @param User $user
	 */
	public function updateUserPassword(User $user){
		try{
			$p = parent::$PDO->prepare("UPDATE $this->userTable SET `password`=:passwd WHERE ID=:id");
			$p->bindValue(":passwd", $user->getPasswd(), PDO::PARAM_STR);
			$p->bindValue(":id", $user->getUserID(), PDO::PARAM_INT);
			$p->execute();
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Updates only a user's email verification and password recovery codes in the database
	 * @param User $user
	 */
	public function updateUserEmailPasswordCodes(User $user){
		try{
			$p = parent::$PDO->prepare("UPDATE $this->userTable SET emailVerificationCodes=:email,
 				passwordRecoveryCodes=:pass WHERE ID=:id");
			$p->bindValue(":email", json_encode($user->getEmailVerificationCodes()), PDO::PARAM_STR);
			$p->bindValue(":pass", json_encode($user->getPasswordRecoveryCodes()), PDO::PARAM_STR);
			$p->bindValue(":id", $user->getUserID(), PDO::PARAM_INT);
			$p->execute();
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Updates the user database from a given \User object
	 * @param User $user
	 * @throws \PDOException
	 */
	public function updateUser(User $user){
		try{
			$p = parent::$PDO->prepare("UPDATE $this->userTable SET email=:email, firstname=:fname,
 			lastname=:lname, gender=:gender, feedLength=:feedLen, username=:uname, webID=:webID,
 			feedDetails=:feedDetails,privateFeed=:privateFeed,emailVerified=:emailVerified,emailVerificationCodes=:emailCodes,
 			passwordRecoveryCodes=:passCodes WHERE ID=:id");
			$p->bindValue(":id", $user->getUserID(), PDO::PARAM_INT);
			$p->bindValue(":email", $user->getEmail(), PDO::PARAM_STR);
			$p->bindValue(":fname", $user->getFname(), PDO::PARAM_STR);
			$p->bindValue(":lname", $user->getLname(), PDO::PARAM_STR);
			$p->bindValue(":gender", $user->getGender(), PDO::PARAM_INT);
			$p->bindValue(":feedLen", $user->getFeedLength(), PDO::PARAM_INT);
			$p->bindValue(":uname", $user->getUsername(), PDO::PARAM_STR);
			$p->bindValue(":webID", $user->getWebID(), PDO::PARAM_STR);
			$p->bindValue(":feedDetails", json_encode($user->getFeedDetails()), PDO::PARAM_STR);
			$p->bindValue(":privateFeed", $user->isPrivateFeed(), PDO::PARAM_BOOL);
			$p->bindValue(":emailVerified", $user->isEmailVerified(), PDO::PARAM_BOOL);
			$p->bindValue(":emailCodes", json_encode($user->getEmailVerificationCodes()), PDO::PARAM_STR);
			$p->bindValue(":passCodes", json_encode($user->getPasswordRecoveryCodes()), PDO::PARAM_STR);
			$p->execute();
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Generates SQL query to make a column. Returns something in the form of `columnName` columnType NULL/Not
	 * Default Key Extra
	 * @param $c Array dictionary representing a column's correct schema
	 * @return string
	 */
	private function makeColumnSQL($c){
		$columnText = "`".$c["Field"]."` ".$c["Type"];
		if($c["Null"] == "NO"){
			$columnText .= " NOT NULL";
		}
		else{
			$columnText .= " NULL";
		}
		if($c["Default"] != null){
			$columnText .= " DEFAULT ".$c["Default"];
		}
		if($c["Key"] == "PRI"){
			$columnText .= " PRIMARY KEY";
		}
		if($c["Extra"] != ""){
			$columnText .= " ".$c["Extra"];
		}
		return $columnText;
	}

	/**
	 * Generate the tables in the current database
	 * @param int $code
	 * @return void
	 * @throws \PDOException
	 */
	public function makeDB($code = 1){
		$generalSetupSQL = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
							SET time_zone = \"+00:00\";";

		$preProcessSQL = "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
						  /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
						  /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
						  /*!40101 SET NAMES utf8mb4 */;";

		$postProcessSQL = "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
						   /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
						   /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";

		$userTableSQL = "CREATE TABLE `".$this->userTable."` (";
		foreach($this->userCorrect as $column){
			$userTableSQL .= $this->makeColumnSQL($column).",";
		}
		$userTableSQL = substr($userTableSQL, 0, strlen($userTableSQL)-1);
		$userTableSQL .= ") CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;";

		$feedTableSQL = "CREATE TABLE `".$this->feedTable."` (";
		foreach($this->feedCorrect as $column){
			$feedTableSQL .= $this->makeColumnSQL($column).",";
		}
		$feedTableSQL = substr($feedTableSQL, 0, strlen($feedTableSQL)-1);
		$feedTableSQL .= ")CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;";

		if($code == 1){
			try{
				// Execute all the statements
				$p = parent::$PDO->prepare($generalSetupSQL.$preProcessSQL.
					$userTableSQL.$feedTableSQL.$postProcessSQL);
				$p->execute();
			}catch(PDOException $e){
				echo "Database creation failed! ".$e->getMessage();
				error_log("Database creation failed! ".$e->getMessage());
				throw $e;
			}
		}
		else if($code == 2){
			$this->updateDBSchema();
		}
	}

	protected function updateDBSchema(){
		try{
			$userTableSchema = $this->describeTable($this->userTable);
			$feedTableSchema = $this->describeTable($this->feedTable);
			$alterSQL = $this->makeAlterQuery([$this->userTable => $userTableSchema,
											   $this->feedTable =>$feedTableSchema],
											  [$this->userTable => $this->userCorrect,
											   $this->feedTable => $this->feedCorrect]);
			$p = parent::$PDO->prepare($alterSQL);
			$p->execute();
		}
		catch(PDOException $e){
			echo "Database update failed! ".$e->getMessage();
			error_log("Database update failed! ".$e->getMessage());
			throw $e;
		}
	}

	/**
	 * Generates SQL query to add missing columns to the given tables
	 * @param $currentTables Array dictionary in the form of ["tableName"=>[table_schema]] representing the values
	 * that are currently existing in the database
	 * @param $correctTables Array dictionary in the form of ["tableName"=>[table_schema]] representing the correct
	 * values
	 * @return string
	 */
	private function makeAlterQuery($currentTables, $correctTables){
		$sql = "";
		// Loop through the given tables
		foreach($correctTables as $tableName=>$table){
			// Loop through all the columns in a table
			foreach($table as $i=>$correct){
				// Check if the current column is in the existing database table
				if(!in_array($correct, $currentTables[$tableName], true)){
					$sql .= "ALTER TABLE `".$tableName."` ADD ".$this->makeColumnSQL($correct);
					if($i == 0){
						$sql .= " FIRST";
					}
					if($i > 0){
						$sql .= " AFTER `".$table[$i-1]["Field"]."`";
					}
					$sql .= ";";
				}
			}
		}
		return $sql;
	}

	/**
	 * Function to return a list of database tables
	 * @return array
	 */
	protected function getDatabaseTables(){
		$p = parent::$PDO->prepare("SHOW TABLES");
		$p->execute();
		$rows = $p->fetchAll(PDO::FETCH_ASSOC);
		$tables = [];
		foreach($rows as $r){
			$tables[] = array_values($r)[0];
		}
		return $tables;
	}

	/**
	 * Function to get layout of a specific table
	 * @param $table string Table to get layout of
	 * @return array
	 */
	protected function describeTable($table){
		$p = parent::$PDO->prepare("DESCRIBE $table");
		$p->execute();
		return $p->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Correct layout of the user table
	 * @var array
	 */
	protected $userCorrect = [
	["Field"=>"ID", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"PRI", "Default"=>null, "Extra"=>"auto_increment"],
	["Field"=>"username", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"password", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"email", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"firstname", "Type"=>"mediumtext", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"lastname", "Type"=>"mediumtext", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"gender", "Type"=>"mediumtext", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"webID", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"feedText", "Type"=>"longtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"feedLength", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"feedDetails", "Type"=>"mediumtext", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"privateFeed", "Type"=>"tinyint(1)", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"emailVerified", "Type"=>"tinyint(1)", "Null"=>"NO", "Key"=>"", "Default"=>"0", "Extra"=>""],
	["Field"=>"emailVerificationCodes", "Type"=>"text", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"passwordRecoveryCodes", "Type"=>"text", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""]
	];

	/**
	 * Correct layout of the feed table
	 * @var array
	 */
	protected $feedCorrect = [
	["Field"=>"ID", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"PRI", "Default"=>null, "Extra"=>"auto_increment"],
	["Field"=>"userID", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"URL", "Type"=>"text", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"orderID", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"videoID", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"videoAuthor", "Type"=>"text", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"description", "Type"=>"text", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"videoTitle", "Type"=>"text", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"duration", "Type"=>"int(11)", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
	["Field"=>"timeAdded", "Type"=>"timestamp", "Null"=>"NO", "Key"=>"", "Default"=>"CURRENT_TIMESTAMP", "Extra"=>""]
	];

	/**
	 * Verifies the currently connected database against the current schema
	 * @return int Returns 0 if all is well, 1 if the user table or feed table do not exist, and 2 if the tables exist but the schema inside is wrong
	 * @throws \PDOException
	 */
	public function verifyDB(){
		try{
			$tables = $this->getDatabaseTables();
			if(!in_array($this->userTable, $tables, true) || !in_array($this->feedTable, $tables, true)){
				return 1;
			}

			$userTableSchema = $this->describeTable($this->userTable);
			$feedTableSchema = $this->describeTable($this->feedTable);

			if($this->verifySchema($this->userCorrect, $userTableSchema) && $this->verifySchema($this->feedCorrect,
					$feedTableSchema)){
				return 0;
			}
			else{
				return 2;
			}
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * Checks if two arrays are equal to test that SQL schemas are compliant
	 * @param $correct
	 * @param $existing
	 * @return bool
	 */
	private function verifySchema($correct, $existing){
		sort($correct);
		sort($existing);
		return $correct == $existing;
	}

	/**
	 * Returns an array of YouTube video IDs that can be safely deleted
	 * @return array
	 */
	public function getPrunableVideos(){
		$feedTable = $this->feedTable;
		$userTable = $this->userTable;
		try{
			$pruneSQL = "SELECT `videoID`,
							MIN((MaxOrderID-orderID)>=feedLength) AS `isUnNeeded`
							FROM
								(SELECT
								`".$feedTable."`.`userID`,
								`".$userTable."`.`feedLength`,
								videoID,
								orderID
							  FROM
								`".$feedTable."`
							  INNER JOIN
								`".$userTable."` ON `".$feedTable."`.`userID` = `".$userTable."`.`ID`) Y
						    INNER JOIN (SELECT `userID`, MAX(`orderID`) AS MaxOrderID FROM `".$feedTable."` GROUP BY `userID`) AS X
						        ON X.userID=Y.`userID`
						    GROUP BY `videoID`
						    ORDER BY `isUnNeeded` DESC";
			$p = parent::$PDO->prepare($pruneSQL);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			$pruneArray = [];

			foreach($rows as $r){
				if($r["isUnNeeded"] == 0){
					continue;
				}
				$pruneArray[] = $r["videoID"];
			}
			return $pruneArray;
		}
		catch(PDOException $e){
			throw $e;
		}
	}

}
