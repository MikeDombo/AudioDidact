<?php
spl_autoload_register(function($class){
	require_once __DIR__.'/DAL.php';
});
date_default_timezone_set('UTC');
mb_internal_encoding("UTF-8");

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
	 *
	 * @param $host
	 * @param $db
	 * @param $username
	 * @param $password
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
	 * @param $username
	 * @return bool
	 */
	public function usernameExists($username){
		return parent::usernameExists(strtolower($username));
	}

	/**
	 * @param $email
	 * @return bool
	 */
	public function emailExists($email){
		return parent::emailExists($email);
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
				throw new Exception("More than one result returned!");
			}
			if(count($rows) == 0){
				return null;
			}
			$rows = $rows[0];

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
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
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
				throw new Exception("More than one result returned!");
			}
			if(count($rows) == 0){
				return null;
			}
			$rows = $rows[0];

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
				throw new Exception("More than one result returned!");
			}
			if(count($rows) == 0){
				return null;
			}
			$rows = $rows[0];

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
				throw new Exception("More than one result returned!");
			}
			if(count($rows) == 0){
				return null;
			}
			$rows = $rows[0];

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
				throw new Exception("No results returned!");
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
	 * @throws \Exception
	 */
	public function addUser(User $user){
		if(!$this->usernameExists($user->getUsername()) && !$this->emailExists($user->getEmail())){
			try{
				$p = parent::$PDO->prepare("INSERT INTO $this->usertable (username, password, email, firstname, 
				lastname, 
			gender, webID,feedLength) VALUES (:username,:password,:email,:fname,:lname,:gender,:webID,:feedLength)");
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
			$p = parent::$PDO->prepare("SELECT * FROM $this->feedTable WHERE userID=:userid ORDER BY orderID DESC LIMIT 
			1");
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_INT);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) == 0){
				$order = 1;
			}
			else{
				$order = intval($rows[0]["orderID"]) + 1;
			}

			$p = parent::$PDO->prepare("INSERT INTO $this->feedTable (userID, videoID, videoAuthor, description, 
			videoTitle, 
		duration, orderID, timeAdded) VALUES (:userID,:videoID,:videoAuthor,:description,:videoTitle,:duration,
		:orderID,:time)");
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
			$p = parent::$PDO->prepare("SELECT * FROM $this->feedTable WHERE userID=:userid ORDER BY orderID DESC 
			LIMIT ".$user->getFeedLength());
			$p->bindValue(":userid", $user->getUserID(), PDO::PARAM_INT);
			$p->execute();
			$rows = $p->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) < 1){
				return null;
				throw new Exception("No results returned!");
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
			if(count($rows)>0){
				return true;
			}
			else{
				return false;
			}
		}
		catch(PDOException $e){
			echo "ERROR: ".$e->getMessage();
			throw $e;
		}
	}

	/**
	 *
	 */
	protected function makeDB(){
		// TODO: Implement makeDB() method.
	}

	/**
	 *
	 */
	protected function verifyDB(){
		// TODO: Implement verifyDB() method.
	}
}
?>