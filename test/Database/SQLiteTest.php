<?php
require_once __DIR__ . "/../../src/header.php";
chdir(__DIR__ . "/../../src/");

use AudioDidact\DB\SQLite;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AudioDidact\DB\SQLite
 * @covers \AudioDidact\DB\MySQLDAL
 * Class SQLiteTest
 */
class SQLiteTest extends TestCase {
	/** @var \PDO $pdo */
	private static $pdo;

	public static function setUpBeforeClass(){
		self::$pdo = new \PDO("sqlite::memory:", null, null, [\PDO::ATTR_PERSISTENT => true]);
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
		$dal = new SQLite(null, self::$pdo);
		$this->assertEquals(1, $dal->verifyDB());
		$dal->makeDB();

		$q = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;")->fetchAll(PDO::FETCH_ASSOC);

		$count = 0;
		foreach($q as $a){
			if($a["name"] === "feed" || $a["name"] === "users"){
				$count += 1;
			}
		}

		$this->assertEquals(2, $count);

		$q = $pdo->query("PRAGMA table_info([feed]);")->fetchAll(PDO::FETCH_ASSOC);
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

		$q = $pdo->query("PRAGMA table_info([users]);")->fetchAll(PDO::FETCH_ASSOC);
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

	public function testAddUser(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();

		$dal->addUser($u);

		$u1 = $dal->getUserByID(1);
		$this->assertEquals($u, $u1);
		$u2 = $dal->getUserByUsername("michael");
		$this->assertEquals($u, $u2);
		$u3 = $dal->getUserByEmail("michael@mike.com");
		$this->assertEquals($u, $u3);
		$u4 = $dal->getUserByWebID("michael");
		$this->assertEquals($u, $u4);
	}

	public function testUpdatePassword(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();

		$dal->addUser($u);

		$u1 = $dal->getUserByID(1);
		$this->assertEquals($u->getPasswd(), $u1->getPasswd());
		$u->setPasswdDB("ABC123");
		$dal->updateUserPassword($u);
		$u2 = $dal->getUserByID(1);
		$this->assertEquals($u->getPasswd(), $u2->getPasswd());
	}

	public function testUpdateUser(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();

		$dal->addUser($u);

		$u1 = $dal->getUserByID(1);
		$this->assertEquals($u->getPasswd(), $u1->getPasswd());
		$u->setWebID("ABC123");
		$dal->updateUser($u);
		$u2 = $dal->getUserByWebID("ABC123");
		$this->assertEquals($u, $u2);
	}

	public function testAddVideo(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();
		$dal->addUser($u);

		$v = $this->generateVideo();
		$dal->addVideo($v, $u);
		$currentFeed = $dal->getFeed($u);

		$this->assertCount(1, $currentFeed);
		$v1 = $currentFeed[0];
		$this->assertEquals($v, $v1);
	}

	public function testUpdateVideo(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();
		$dal->addUser($u);

		$v = $this->generateVideo();
		$dal->addVideo($v, $u);
		$currentFeed = $dal->getFeed($u);

		$this->assertCount(1, $currentFeed);
		$v1 = $currentFeed[0];
		$this->assertEquals($v, $v1);

		$v->setDuration(100);
		$dal->updateVideo($v, $u);
		$currentFeed = $dal->getFeed($u);

		$this->assertCount(1, $currentFeed);
		$v1 = $currentFeed[0];
		$this->assertEquals($v, $v1);
	}

	public function testGetFeed(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();
		$dal->addUser($u);

		for($i = 0; $i < $u->getFeedLength()*2; $i++){
			$v = $this->generateVideo();
			$v->setId($i);
			$dal->addVideo($v, $u);
		}

		$feed = $dal->getFeed($u);
		$this->assertCount($u->getFeedLength(), $feed);
		for($i = 0; $i < count($feed); $i++){
			$this->assertEquals(($u->getFeedLength()*2) - $i, $feed[$i]->getOrder());
		}
	}

	public function testGetFeedHistory(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();
		$dal->addUser($u);

		for($i = 0; $i < $u->getFeedLength()*2; $i++){
			$v = $this->generateVideo();
			$v->setId($i);
			$dal->addVideo($v, $u);
		}

		$feed = $dal->getFullFeedHistory($u);
		$this->assertCount($u->getFeedLength()*2, $feed);
		for($i = 0; $i < count($feed); $i++){
			$this->assertEquals(($u->getFeedLength()*2) - $i, $feed[$i]->getOrder());
		}
	}

	public function testGetPrunableVideos(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();
		$dal->addUser($u);

		for($i = 0; $i < $u->getFeedLength()*2; $i++){
			$v = $this->generateVideo();
			$v->setId($i);
			$dal->addVideo($v, $u);
		}

		$canBeDeleted = $dal->getPrunableVideos();
		$this->assertCount(10, $canBeDeleted);
		foreach($canBeDeleted as $v){
			$this->assertLessThan(10, $v);
		}
	}

	public function testSetFeedText(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();
		$dal->addUser($u);

		$dal->setFeedText($u, "123456...");
		$u1 = $dal->getUserByID(1);
		$this->assertEquals("123456...", $u1->getFeedText());
	}

	public function testUpdateUserEmailPasswordCodes(){
		$this->clearDB();
		$dal = new SQLite(null, self::$pdo);
		$u = $this->generateUser();

		$dal->addUser($u);
		$codes = $dal->getUserByID(1)->getPasswordRecoveryCodes();
		$this->assertEmpty($codes);
		$u->addPasswordRecoveryCode();
		$dal->updateUserEmailPasswordCodes($u);
		$codes = $dal->getUserByID(1)->getPasswordRecoveryCodes();
		$this->assertNotEmpty($codes);
		$this->assertEquals($u->getPasswordRecoveryCodes(), $codes);

		$u->setPasswordRecoveryCodes([]);

		$codes = $dal->getUserByID(1)->getEmailVerificationCodes();
		$this->assertEmpty($codes);
		$u->addEmailVerificationCode();
		$dal->updateUserEmailPasswordCodes($u);
		$codes = $dal->getUserByID(1)->getEmailVerificationCodes();
		$this->assertNotEmpty($codes);
		$this->assertEquals($u->getEmailVerificationCodes(), $codes);
		$codes = $dal->getUserByID(1)->getPasswordRecoveryCodes();
		$this->assertEmpty($codes);
	}

	private function clearUsers(){
		$pdo = self::$pdo;
		$pdo->exec("DELETE FROM users; delete from sqlite_sequence where name='users';");
	}

	private function clearVideos(){
		$pdo = self::$pdo;
		$pdo->exec("DELETE FROM feed; delete from sqlite_sequence where name='feed';");
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
