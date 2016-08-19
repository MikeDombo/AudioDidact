<?php


/**
 * Class MySQLDAL
 *
 */
class MySQLDAL extends DAL{
	private $usertable = "users";
	private $feedTable = "feed";
	private $host;
	private $db;
	private $username;
	private $password;

	/**
	 * MySQLDAL constructor.
	 * Sets up parent's PDO object using the parameters that are passed in.
	 *
	 * @param $host
	 * @param $db
	 * @param $username
	 * @param $password
	 */
	public function __construct($host, $db, $username, $password){
		if(!CHECK_REQUIRED){
			require_once __DIR__."/../header.php";
		}
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
	 * @param $id
	 * @return null|\User
	 * @throws \Exception
	 */
	public function getUserByID($id){
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->usertable WHERE ID=:id");
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
	 * @param $rows
	 * @return \User
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

		return $user;
	}

	/**
	 * @param $webID
	 * @return null|\User
	 * @throws \Exception
	 */
	public function getUserByWebID($webID){
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->usertable WHERE webID=:id");
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
	 * @param $username
	 * @return \User
	 * @throws \Exception
	 */
	public function getUserByUsername($username){
		$username = strtolower($username);
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->usertable WHERE username=:username");
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
	 * @param $email
	 * @return \User
	 * @throws \Exception
	 */
	public function getUserByEmail($email){
		$email = strtolower($email);
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->usertable WHERE email=:email");
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
	 * @param \User $user
	 * @param $id
	 * @return null|\Video
	 * @throws \Exception
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
			return $vid;
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 * @param \User $user
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function addUser(User $user){
		if(!$this->usernameExists($user->getUsername()) && !$this->emailExists($user->getEmail())){
			try{
				$p = parent::$PDO->prepare("INSERT INTO $this->usertable (username, password, email, firstname,
				lastname, gender, webID, feedLength) VALUES (:username,:password,:email,:fname,:lname,:gender,:webID,
				:feedLength)");
				$p->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
				$p->bindValue(':password', $user->getPasswd(), PDO::PARAM_STR);
				$p->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
				$p->bindValue(':fname', $user->getFname(), PDO::PARAM_STR);
				$p->bindValue(':lname', $user->getLname(), PDO::PARAM_STR);
				$p->bindValue(':gender', $user->getGender(), PDO::PARAM_STR);
				$p->bindValue(':webID', $user->getWebID(), PDO::PARAM_STR);
				$p->bindValue(':feedLength', $user->getFeedLength(), PDO::PARAM_INT);
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
	 * @param \Video $vid
	 * @param \User $user
	 * @return bool
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
			$p = parent::$PDO->prepare("INSERT INTO $this->feedTable (userID, videoID, videoAuthor, description,
			videoTitle, duration, orderID, timeAdded) VALUES (:userID,:videoID,:videoAuthor,:description,:videoTitle,
			:duration, :orderID,:time)");
			$p->bindValue(":userID", $user->getUserID(), PDO::PARAM_INT);
			$p->bindValue(":videoID", $vid->getId(), PDO::PARAM_STR);
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
	 * @param \User $user
	 * @return mixed
	 */
	public function getFeedText(User $user){
		try{
			$p = parent::$PDO->prepare("SELECT feedText FROM $this->usertable WHERE id=:userid");
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
	 * @param \User $user
	 * @return array|null
	 * @throws \Exception
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
				$vid->setTitle($row["videoTitle"]);
				$vid->setOrder($row["orderID"]);
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
	 * @param \User $user
	 * @param $feed
	 * @return bool
	 */
	public function setFeedText(User $user, $feed){
		try{
			$p = parent::$PDO->prepare("UPDATE $this->usertable set feedText=:feedText WHERE id=:userid");
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
	 * @param \Video $vid
	 * @param \User $user
	 * @return bool
	 */
	public function inFeed(Video $vid, User $user){
		try{
			$p = parent::$PDO->prepare("SELECT * FROM $this->feedTable WHERE userID=:userid AND videoID=:videoID");
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_STR);
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
	 *
	 */
	public function makeDB(){
		$generalSetupSQL = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
							SET time_zone = \"+00:00\";";

		$preProcessSQL = "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
						/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
						/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
						/*!40101 SET NAMES utf8mb4 */;";

		$postProcessSQL = "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
							/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
							/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";

		$userTableSQL = "CREATE TABLE `".$this->usertable."` (
						  `ID` int(11) NOT NULL,
						  `username` mediumtext COLLATE utf8mb4_bin NOT NULL,
						  `password` mediumtext COLLATE utf8mb4_bin NOT NULL,
						  `email` mediumtext COLLATE utf8mb4_bin NOT NULL,
						  `firstname` mediumtext COLLATE utf8mb4_bin,
						  `lastname` mediumtext COLLATE utf8mb4_bin,
						  `gender` mediumtext COLLATE utf8mb4_bin,
						  `webID` mediumtext COLLATE utf8mb4_bin NOT NULL,
						  `feedText` longtext COLLATE utf8mb4_bin NOT NULL,
						  `feedLength` int(11) NOT NULL
						) 
						ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
						ALTER TABLE `".$this->usertable."`
							ADD PRIMARY KEY (`ID`);
						ALTER TABLE `".$this->usertable."`
							MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";

		$feedTableSQL = "CREATE TABLE `".$this->feedTable."` (
						  `ID` int(11) NOT NULL,
						  `userID` int(11) NOT NULL,
						  `orderID` int(11) NOT NULL,
						  `videoID` mediumtext COLLATE utf8mb4_bin NOT NULL,
						  `videoAuthor` text COLLATE utf8mb4_bin NOT NULL,
						  `description` text COLLATE utf8mb4_bin,
						  `videoTitle` text COLLATE utf8mb4_bin NOT NULL,
						  `duration` int(11) DEFAULT NULL,
						  `timeAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
						) 
						ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
						ALTER TABLE `".$this->feedTable."`
						  ADD PRIMARY KEY (`ID`);
						ALTER TABLE `".$this->feedTable."`
						  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";

		// Execute all the statements
		$p = parent::$PDO->prepare($generalSetupSQL.$preProcessSQL.$userTableSQL.$feedTableSQL.$postProcessSQL);
		$p->execute();
	}

	/**
	 *
	 */
	public function verifyDB(){
		try{
			$p = parent::$PDO->prepare("SHOW TABLES");
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			$tables = [];
			foreach($rows as $r){
				$tables[] = array_values($r)[0];
			}
			if(!in_array($this->usertable, $tables, true) || !in_array($this->feedTable, $tables, true)){
				return 1;
			}

			$p = parent::$PDO->prepare("DESCRIBE $this->usertable");
			$p->execute();
			$userTableSchema = $p->fetchAll(PDO::FETCH_ASSOC);
			$p = parent::$PDO->prepare("DESCRIBE $this->feedTable");
			$p->execute();
			$feedTableSchema = $p->fetchAll(PDO::FETCH_ASSOC);

			$userCorrect = [
				["Field"=>"ID", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"PRI", "Default"=>null, "Extra"=>"auto_increment",],
				["Field"=>"username", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>"",],
				["Field"=>"password", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>"",],
				["Field"=>"email", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>"",],
				["Field"=>"firstname", "Type"=>"mediumtext", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>"",],
				["Field"=>"lastname", "Type"=>"mediumtext", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>"",],
				["Field"=>"gender", "Type"=>"mediumtext", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>"",],
				["Field"=>"webID", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>"",],
				["Field"=>"feedText", "Type"=>"longtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>"",],
				["Field"=>"feedLength", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>"",]
			];

			$feedCorrect = [
				["Field"=>"ID", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"PRI", "Default"=>null, "Extra"=>"auto_increment"],
				["Field"=>"userID", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
				["Field"=>"orderID", "Type"=>"int(11)", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
				["Field"=>"videoID", "Type"=>"mediumtext", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
				["Field"=>"videoAuthor", "Type"=>"text", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
				["Field"=>"description", "Type"=>"text", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
				["Field"=>"videoTitle", "Type"=>"text", "Null"=>"NO", "Key"=>"", "Default"=>null, "Extra"=>""],
				["Field"=>"duration", "Type"=>"int(11)", "Null"=>"YES", "Key"=>"", "Default"=>null, "Extra"=>""],
				["Field"=>"timeAdded", "Type"=>"timestamp", "Null"=>"NO", "Key"=>"", "Default"=>"CURRENT_TIMESTAMP", "Extra"=>""]
			];

			if($this->verifySchema($userCorrect, $userTableSchema) && $this->verifySchema($feedCorrect,	$feedTableSchema)){
				return 0;
			}
			else{
				return 2;
			}
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			return $e;
		}
	}

	private function verifySchema($correct, $existing){
		sort($correct);
		sort($existing);
		return $correct == $existing;
	}
}
