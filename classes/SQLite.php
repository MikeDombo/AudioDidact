<?php

/**
 * Class SQLite contains methods for communicating with a SQLite database stored on a filesystem
 *
 */
class SQLite extends MySQLDAL{
	/**
	 * SQLite constructor.
	 * Sets up parent's PDO object using the parameters that are passed in.
	 *
	 * @param $filepath string The filepath to the SQLite database file
	 * @throws \PDOException Rethrows any PDO exceptions encountered when connecting to the database
	 */
	public function __construct($filepath, $i, $j, $k){
		if($this->getPDO() == null){
			try{
				$this->setPDO(new PDO('sqlite:'.$filepath));
				$this->getPDO()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch(PDOException $e){
				echo 'ERROR: '.$e->getMessage();
				throw $e;
			}
		}

		parent::__construct("","","","");
	}

	/**
	 * Function to return a list of database tables
	 * @return array
	 */
	protected function getDatabaseTables(){
		$p = parent::$PDO->prepare("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
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
	 * @param $table string table to get layout of
	 * @return array
	 */
	protected function describeTable($table){
		$p = parent::$PDO->prepare("PRAGMA table_info([$table]);");
		$p->execute();
		return $p->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Generate the tables in the current database
	 *
	 * @param int $code
	 * @return void
	 * @throws \PDOException
	 */
	public function makeDB($code = 1){
		$userTableSQL = "CREATE TABLE `".$this->userTable."` (
						  `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
						  `username` mediumtext NOT NULL,
						  `password` mediumtext NOT NULL,
						  `email` mediumtext NOT NULL,
						  `firstname` mediumtext,
						  `lastname` mediumtext,
						  `gender` mediumtext,
						  `webID` mediumtext NOT NULL,
						  `feedText` longtext NOT NULL,
						  `feedLength` int(11) NOT NULL,
						  `feedDetails` mediumtext NULL,
						  `privateFeed` tinyint(1) NOT NULL
						);";

		$feedTableSQL = "CREATE TABLE `".$this->feedTable."` (
						  `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
						  `userID` int(11) NOT NULL,
						  `orderID` int(11) NOT NULL,
						  `URL` mediumtext NULL,
						  `videoID` mediumtext NOT NULL,
						  `videoAuthor` text NOT NULL,
						  `description` text,
						  `videoTitle` text NOT NULL,
						  `duration` int(11) DEFAULT NULL,
						  `timeAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
						);";

		if($code == 1){
			try{
				// Execute all the statements
				$p = parent::$PDO->prepare($userTableSQL);
				$p->execute();
				$p = parent::$PDO->prepare($feedTableSQL);
				$p->execute();
			}catch(PDOException $e){
				echo "Database creation failed! ".$e->getMessage();
				error_log("Database creation failed! ".$e->getMessage());
				throw $e;
			}
		}
		else if($code == 2){
			try{
				$p = parent::$PDO->prepare("ALTER TABLE $this->feedTable ADD `URL` mediumtext NULL AFTER `orderID`");
				$p->execute();
			}catch(PDOException $e){
				echo "Database update failed! ".$e->getMessage();
				error_log("Database update failed! ".$e->getMessage());
				throw $e;
			}
		}
	}

	/**
	 * Correct layout of the user table
	 * @var array
	 */
	protected $userCorrect = [['cid' => '0', 'name' => 'ID', 'type' => 'INTEGER', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '1'],
		['cid' => '1', 'name' => 'username', 'type' => 'mediumtext', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '2', 'name' => 'password', 'type' => 'mediumtext', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '3', 'name' => 'email', 'type' => 'mediumtext', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '4', 'name' => 'firstname', 'type' => 'mediumtext', 'notnull' => '0', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '5', 'name' => 'lastname', 'type' => 'mediumtext', 'notnull' => '0', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '6', 'name' => 'gender', 'type' => 'mediumtext', 'notnull' => '0', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '7', 'name' => 'webID', 'type' => 'mediumtext', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '8', 'name' => 'feedText', 'type' => 'longtext', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '9', 'name' => 'feedLength', 'type' => 'int(11)', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '10', 'name' => 'feedDetails', 'type' => 'mediumtext', 'notnull' => '0', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '11', 'name' => 'privateFeed', 'type' => 'tinyint(1)', 'notnull' => '1', 'dflt_value' => NULL, 'pk'=> '0']];

	/**
	 * Correct layout of the feed table
	 * @var array
	 */
	protected $feedCorrect = [['cid' => '0', 'name' => 'ID', 'type' => 'INTEGER', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '1'],
		['cid' => '1', 'name' => 'userID', 'type' => 'int(11)', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '2', 'name' => 'orderID', 'type' => 'int(11)', 'notnull' => '1','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '3', 'name' => 'URL', 'type' => 'mediumtext', 'notnull' => '0','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '3', 'name' => 'videoID', 'type' => 'mediumtext', 'notnull' => '1','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '4', 'name' => 'videoAuthor', 'type' => 'text', 'notnull' => '1','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '5', 'name' => 'description', 'type' => 'text', 'notnull' => '0','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '6', 'name' => 'videoTitle', 'type' => 'text', 'notnull' => '1','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '7', 'name' => 'duration', 'type' => 'int(11)', 'notnull' => '0','dflt_value' => 'NULL', 'pk' => '0'],
		['cid' => '8', 'name' => 'timeAdded', 'type' => 'timestamp', 'notnull' => '1','dflt_value' => 'CURRENT_TIMESTAMP', 'pk' => '0']];

}
