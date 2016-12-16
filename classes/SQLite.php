<?php

class SQLite extends MySQLDAL{
	/**
	 * SQLite constructor.
	 * Sets up parent's PDO object using the parameters that are passed in.
	 *
	 * @param string The filepath to the SQLite database file
	 * @throws \PDOException Rethrows any PDO exceptions encountered when connecting to the database
	 */
	public function __construct($filepath, $i, $j, $k){
		if(!CHECK_REQUIRED){
			require_once __DIR__."/../header.php";
		}

		if($this->getPDO() == null){
			try{
				$this->setPDO(new PDO('sqlite:'.$filepath));
				$this->getPDO()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch(PDOException $e){
				echo 'ERROR: '.$e->getMessage();
				throw $e;
			}
		}
	}

	protected $collation = "BINARY";

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

	protected function describeTable($table){
		$p = parent::$PDO->prepare("PRAGMA table_info([$table]);");
		$p->execute();
		return $p->fetchAll(PDO::FETCH_ASSOC);
	}

	public function makeDB($code = 1){
		$userTableSQL = "CREATE TABLE `".$this->usertable."` (
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
				$p = parent::$PDO->prepare("");
				$p->execute();
			}catch(PDOException $e){
				echo "Database update failed! ".$e->getMessage();
				error_log("Database update failed! ".$e->getMessage());
				throw $e;
			}
		}
	}

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


	protected $feedCorrect = [['cid' => '0', 'name' => 'ID', 'type' => 'INTEGER', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '1'],
		['cid' => '1', 'name' => 'userID', 'type' => 'int(11)', 'notnull' => '1', 'dflt_value' => NULL, 'pk' => '0'],
		['cid' => '2', 'name' => 'orderID', 'type' => 'int(11)', 'notnull' => '1','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '3', 'name' => 'videoID', 'type' => 'mediumtext', 'notnull' => '1','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '4', 'name' => 'videoAuthor', 'type' => 'text', 'notnull' => '1','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '5', 'name' => 'description', 'type' => 'text', 'notnull' => '0','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '6', 'name' => 'videoTitle', 'type' => 'text', 'notnull' => '1','dflt_value' => NULL, 'pk' => '0'],
		['cid' => '7', 'name' => 'duration', 'type' => 'int(11)', 'notnull' => '0','dflt_value' => 'NULL', 'pk' => '0'],
		['cid' => '8', 'name' => 'timeAdded', 'type' => 'timestamp', 'notnull' => '1','dflt_value' => 'CURRENT_TIMESTAMP', 'pk' => '0']];

	/**
	 * Builds a SQL query that checks if a column exists in a given table and adds the new column if it doesn't exist
	 * @param $tableName
	 * @param $columnName
	 * @param $alterQuery
	 * @return string
	 */
	private function makeAlterQuery($tableName, $columnName, $alterQuery){
		return "IF NOT EXISTS( SELECT NULL
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE table_name = '$tableName'
                    AND table_schema = '".DB_DATABASE."'
                    AND column_name = '$columnName')  THEN
				    $alterQuery
				END IF;";
	}
}
