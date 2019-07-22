<?php
require_once __DIR__ . "/../../src/header.php";
chdir(__DIR__ . "/../../src/");

use AudioDidact\DB\MySQLDAL;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AudioDidact\DB\MySQLDAL
 * Class MySQLDALTest
 */
class MySQLDALTest extends TestCase {
	/** @var \PDO $pdo */
	private static $pdo;

	public static function setUpBeforeClass(): void {
		$dbPass = getenv("AD_MYSQL_TEST_PASSWORD");
		if($dbPass === false){
			$dbPass = DB_PASSWORD;
		}
		$dbUser = getenv("AD_MYSQL_TEST_USER");
		if($dbUser === false){
			$dbUser = DB_USER;
		}
		$dbHost = getenv("AD_MYSQL_TEST_HOST");
		if($dbHost === false){
			$dbHost = mb_split(";", PDO_STR)[0];
		}
		self::$pdo = new \PDO($dbHost, $dbUser, $dbPass);
		self::$pdo->exec("CREATE SCHEMA phpunit_audiodidact_test; USE phpunit_audiodidact_test");
	}

	public static function tearDownAfterClass(): void {
		self::$pdo->exec("DROP SCHEMA phpunit_audiodidact_test");
	}

	private static function assertArrayContains($needle, $haystack){
		$found = in_array($needle, $haystack, true);

		foreach($haystack as $h){
			if(is_array($h)){
				$found = self::assertArrayContains($needle, $h) ? true : $found;
			}
		}
		return $found;
	}

	public function testMakeDB(){
		$pdo = self::$pdo;
		$dal = new MySQLDAL(null, self::$pdo);
		$this->assertEquals(1, $dal->verifyDB());
		$dal->makeDB();

		$q = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_ASSOC);

		$this->assertTrue($this->assertArrayContains("feed", $q));
		$this->assertTrue($this->assertArrayContains("users", $q));

		$q = $pdo->query("DESCRIBE feed;")->fetchAll(PDO::FETCH_ASSOC);
		$this->assertTrue($this->assertArrayContains("ID", $q));
		$this->assertTrue($this->assertArrayContains("timeAdded", $q));
		$this->assertTrue($this->assertArrayContains("userID", $q));
		$this->assertTrue($this->assertArrayContains("orderID", $q));
		$this->assertTrue($this->assertArrayContains("filename", $q));
		$this->assertTrue($this->assertArrayContains("thumbnailFilename", $q));
		$this->assertTrue($this->assertArrayContains("URL", $q));
		$this->assertTrue($this->assertArrayContains("videoID", $q));
		$this->assertTrue($this->assertArrayContains("videoAuthor", $q));
		$this->assertTrue($this->assertArrayContains("description", $q));
		$this->assertTrue($this->assertArrayContains("videoTitle", $q));
		$this->assertTrue($this->assertArrayContains("duration", $q));
		$this->assertTrue($this->assertArrayContains("isVideo", $q));

		$q = $pdo->query("DESCRIBE users;")->fetchAll(PDO::FETCH_ASSOC);
		$this->assertTrue($this->assertArrayContains("ID", $q));
		$this->assertTrue($this->assertArrayContains("username", $q));
		$this->assertTrue($this->assertArrayContains("password", $q));
		$this->assertTrue($this->assertArrayContains("email", $q));
		$this->assertTrue($this->assertArrayContains("firstname", $q));
		$this->assertTrue($this->assertArrayContains("lastname", $q));
		$this->assertTrue($this->assertArrayContains("gender", $q));
		$this->assertTrue($this->assertArrayContains("webID", $q));
		$this->assertTrue($this->assertArrayContains("feedText", $q));
		$this->assertTrue($this->assertArrayContains("feedLength", $q));
		$this->assertTrue($this->assertArrayContains("feedDetails", $q));
		$this->assertTrue($this->assertArrayContains("privateFeed", $q));
		$this->assertTrue($this->assertArrayContains("emailVerified", $q));
		$this->assertTrue($this->assertArrayContains("emailVerificationCodes", $q));
		$this->assertTrue($this->assertArrayContains("passwordRecoveryCodes", $q));
	}

	public function testUpdateDB(){
		$pdo = self::$pdo;
		$dal = new MySQLDAL(null, self::$pdo);

		$pdo->exec("ALTER TABLE users drop lastname");
		$pdo->exec("ALTER TABLE feed drop URL");

		$this->assertEquals(2, $dal->verifyDB());
		$dal->makeDB(2);

		$q = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_ASSOC);

		$this->assertTrue($this->assertArrayContains("feed", $q));
		$this->assertTrue($this->assertArrayContains("users", $q));

		$q = $pdo->query("DESCRIBE feed;")->fetchAll(PDO::FETCH_ASSOC);
		$this->assertTrue($this->assertArrayContains("ID", $q));
		$this->assertTrue($this->assertArrayContains("timeAdded", $q));
		$this->assertTrue($this->assertArrayContains("userID", $q));
		$this->assertTrue($this->assertArrayContains("orderID", $q));
		$this->assertTrue($this->assertArrayContains("filename", $q));
		$this->assertTrue($this->assertArrayContains("thumbnailFilename", $q));
		$this->assertTrue($this->assertArrayContains("URL", $q));
		$this->assertTrue($this->assertArrayContains("videoID", $q));
		$this->assertTrue($this->assertArrayContains("videoAuthor", $q));
		$this->assertTrue($this->assertArrayContains("description", $q));
		$this->assertTrue($this->assertArrayContains("videoTitle", $q));
		$this->assertTrue($this->assertArrayContains("duration", $q));
		$this->assertTrue($this->assertArrayContains("isVideo", $q));

		$q = $pdo->query("DESCRIBE users;")->fetchAll(PDO::FETCH_ASSOC);
		$this->assertTrue($this->assertArrayContains("ID", $q));
		$this->assertTrue($this->assertArrayContains("username", $q));
		$this->assertTrue($this->assertArrayContains("password", $q));
		$this->assertTrue($this->assertArrayContains("email", $q));
		$this->assertTrue($this->assertArrayContains("firstname", $q));
		$this->assertTrue($this->assertArrayContains("lastname", $q));
		$this->assertTrue($this->assertArrayContains("gender", $q));
		$this->assertTrue($this->assertArrayContains("webID", $q));
		$this->assertTrue($this->assertArrayContains("feedText", $q));
		$this->assertTrue($this->assertArrayContains("feedLength", $q));
		$this->assertTrue($this->assertArrayContains("feedDetails", $q));
		$this->assertTrue($this->assertArrayContains("privateFeed", $q));
		$this->assertTrue($this->assertArrayContains("emailVerified", $q));
		$this->assertTrue($this->assertArrayContains("emailVerificationCodes", $q));
		$this->assertTrue($this->assertArrayContains("passwordRecoveryCodes", $q));
	}

	private function clearUsers(){
		$pdo = self::$pdo;
		$pdo->exec("TRUNCATE users; ALTER TABLE users AUTO_INCREMENT = 1;");
	}

	private function clearVideos(){
		$pdo = self::$pdo;
		$pdo->exec("TRUNCATE feed; ALTER TABLE feed AUTO_INCREMENT = 1;");
	}

	private function clearDB(){
		$this->clearVideos();
		$this->clearUsers();
	}

	private function generateUser(){
		$u = new \AudioDidact\User();
		$u->setUsername("MICHAEL");
		$u->setWebID("michael");
		$u->setFname("Michael");
		$u->setLname("Domb");
		$u->setPasswdDB("ABC");
		$u->setPrivateFeed(false);
		$u->setEmail("michael@mike.com");
		$u->setFeedLength(10);
		$u->setGender(2);
		$u->setUserID(1);
		return $u;
	}

	private function generateVideo($isVideo = false){
		$v = new \AudioDidact\Video();
		$v->setAuthor("Michael");
		$v->setDesc("some\nmultiline\n\tdescription.");
		$v->setDuration(250);
		$v->setFilename("myfile");
		$v->setId("1");
		$v->setIsVideo($isVideo);
		$v->setThumbnailFilename("myfile.png");
		$v->setOrder(1);
		$v->setTime(time());
		$v->setTitle("some good title");
		$v->setURL("http://mywebsite.com/mygreatvideo");

		return $v;
	}
}
