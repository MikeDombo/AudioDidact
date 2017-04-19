<?php
namespace AudioDidact;

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
				$this->setPDO(new \PDO('sqlite:'.$filepath));
				$this->getPDO()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			}catch(\PDOException $e){
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
		$rows = $p->fetchAll(\PDO::FETCH_ASSOC);
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
		return $p->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Generate the tables in the current database
	 * @param int $code
	 * @return void
	 * @throws \PDOException
	 */
	public function makeDB($code = 1){
		if($code == 1){
			try{
				$userTableSQL = "CREATE TABLE `".$this->userTable."` (`ID` INTEGER NOT null PRIMARY KEY AUTOINCREMENT);";
				$feedTableSQL = "CREATE TABLE `".$this->feedTable."` (`ID` INTEGER NOT null PRIMARY KEY AUTOINCREMENT);";
				// Execute all the statements
				$p = parent::$PDO->prepare($userTableSQL);
				$p->execute();
				$p = parent::$PDO->prepare($feedTableSQL);
				$p->execute();
				// Use "updateDBSchema" so that the newly created tables will be updated to the correct schema
				$this->updateDBSchema();
			}
			catch(\PDOException $e){
				echo "Database creation failed! ".$e->getMessage();
				error_log("Database creation failed! ".$e->getMessage());
				throw $e;
			}
		}
		else if($code == 2){
			$this->updateDBSchema();
		}
	}

	/**
	 * Generates SQL query to add missing columns to the given tables
	 * @param $currentTables array dictionary in the form of ["tableName"=>[table_schema]] representing the values
	 * that are currently existing in the database
	 * @param $correctTables array dictionary in the form of ["tableName"=>[table_schema]] representing the correct
	 * values
	 * @return string
	 */
	protected function makeAlterQuery($currentTables, $correctTables){
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
						$sql .= " AFTER `".$table[$i-1]["name"]."`";
					}
					$sql .= ";";
				}
			}
		}
		return $sql;
	}

	/**
	 * Generates SQL query to make a column. Returns something in the form of `columnName` columnType null/Not
	 * Default Key Extra
	 * @param $c array dictionary representing a column's correct schema
	 * @return string
	 */
	private function makeColumnSQL($c){
		$columnText = "`".$c["name"]."` ".$c["type"];
		if($c["notnull"] == "1"){
			$columnText .= " NOT null";
		}
		if($c["dflt_value"] != null){
			$columnText .= " DEFAULT ".$c["Default"];
		}
		if($c["pk"] == "1"){
			$columnText .= " PRIMARY KEY";
		}
		return $columnText;
	}	

	/**
	 * Correct layout of the user table
	 * @var array
	 */
	protected $userCorrect = [
		['cid' => '0', 'name' => 'ID', 'type' => 'INTEGER', 'notnull' => '1', 'dflt_value' => null, 'pk' => '1'],
		['cid' => '1', 'name' => 'username', 'type' => 'mediumtext', 'notnull' => '1', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '2', 'name' => 'password', 'type' => 'mediumtext', 'notnull' => '1', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '3', 'name' => 'email', 'type' => 'mediumtext', 'notnull' => '1', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '4', 'name' => 'firstname', 'type' => 'mediumtext', 'notnull' => '0', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '5', 'name' => 'lastname', 'type' => 'mediumtext', 'notnull' => '0', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '6', 'name' => 'gender', 'type' => 'mediumtext', 'notnull' => '0', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '7', 'name' => 'webID', 'type' => 'mediumtext', 'notnull' => '1', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '8', 'name' => 'feedText', 'type' => 'longtext', 'notnull' => '1', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '9', 'name' => 'feedLength', 'type' => 'int(11)', 'notnull' => '1', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '10', 'name' => 'feedDetails', 'type' => 'mediumtext', 'notnull' => '0', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '11', 'name' => 'privateFeed', 'type' => 'tinyint(1)', 'notnull' => '1', 'dflt_value' => null, 'pk'=> '0'],
		['cid' => '12', "name"=>"emailVerified", "type"=>"tinyint(1)", "notnull"=>"1", "dflt_value"=>"0", "pk"=>"0"],
		['cid' => '13', "name"=>"emailVerificationCodes", "type"=>"mediumtext", "notnull"=>"0", "dflt_value"=>null, "pk"=>"0"],
		['cid' => '14', "name"=>"passwordRecoveryCodes", "type"=>"mediumtext", "notnull"=>"0", "dflt_value"=>null, "pk"=>"0"]
	];
	
	/**
	 * Correct layout of the feed table
	 * @var array
	 */
	protected $feedCorrect = [
		['cid' => '0', 'name' => 'ID', 'type' => 'INTEGER', 'notnull' => '1', 'dflt_value' => null, 'pk' => '1'],
		['cid' => '1', 'name' => 'userID', 'type' => 'int(11)', 'notnull' => '1', 'dflt_value' => null, 'pk' => '0'],
		['cid' => '2', 'name' => 'orderID', 'type' => 'int(11)', 'notnull' => '1','dflt_value' => null, 'pk' => '0'],
		['cid' => '3', "name"=>"filename", "type"=>"mediumtext", "notnull"=>"0", "dflt_value"=>null, "pk"=>"0"],
		['cid' => '4', "name"=>"thumbnailFilename", "type"=>"mediumtext", "notnull"=>"0", "dflt_value"=>null, "pk"=>"0"],
		['cid' => '5', 'name' => 'URL', 'type' => 'mediumtext', 'notnull' => '0','dflt_value' => null, 'pk' => '0'],
		['cid' => '6', 'name' => 'videoID', 'type' => 'mediumtext', 'notnull' => '1','dflt_value' => null, 'pk' => '0'],
		['cid' => '7', 'name' => 'videoAuthor', 'type' => 'text', 'notnull' => '1','dflt_value' => null, 'pk' => '0'],
		['cid' => '8', 'name' => 'description', 'type' => 'text', 'notnull' => '0','dflt_value' => null, 'pk' => '0'],
		['cid' => '9', 'name' => 'videoTitle', 'type' => 'text', 'notnull' => '1','dflt_value' => null, 'pk' => '0'],
		['cid' => '10', 'name' => 'duration', 'type' => 'int(11)', 'notnull' => '0','dflt_value' => 'null', 'pk' => '0'],
		['cid' => '11', "name"=>"isVideo", "type"=>"tinyint(1)", "notnull"=>"1", "dflt_value"=>"0", "pk"=>"0"],
		['cid' => '12', 'name' => 'timeAdded', 'type' => 'timestamp', 'notnull' => '1','dflt_value' => 'CURRENT_TIMESTAMP', 'pk' => '0']
	];

}
