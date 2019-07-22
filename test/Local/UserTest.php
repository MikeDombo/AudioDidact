<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 5/29/2017
 * Time: 7:42 PM
 */

require_once __DIR__ . "/../../src/header.php";
chdir(__DIR__ . "/../../src/");

use AudioDidact\User;
use \PHPUnit\Framework\TestCase;

/**
 * @covers AudioDidact\User
 * Class UserTest
 */
class UserTest extends TestCase{
	public function testConstructor(){
		$u = new User();
		$this->assertInstanceOf(User::class, $u);
		$this->assertEquals(false, $u->isEmailVerified());
		$this->assertEquals([], $u->getEmailVerificationCodes());
		$this->assertEquals([], $u->getPasswordRecoveryCodes());
	}

	public function testVerificationCode(){
		$u = new User();
		$em = $u->addEmailVerificationCode();
		$gotEmail = $u->getEmailVerificationCodes();
		$this->assertCount(1, $gotEmail);
		$this->assertTrue($em == $gotEmail[0]);
		$this->assertArrayHasKey("code", $em);
		$this->assertArrayHasKey("expiration", $em);
		$this->assertIsString($em["code"]);
		$this->assertIsInt($em["expiration"]);
		$this->assertTrue($u->verifyEmailVerificationCode($em["code"]));
		$this->assertFalse($u->verifyEmailVerificationCode(""));

		$em = $u->addPasswordRecoveryCode();
		$gotEmail = $u->getPasswordRecoveryCodes();
		$this->assertCount(1, $gotEmail);
		$this->assertTrue($em == $gotEmail[0]);
		$this->assertArrayHasKey("code", $em);
		$this->assertArrayHasKey("expiration", $em);
		$this->assertIsString($em["code"]);
		$this->assertIsInt($em["expiration"]);
		$this->assertTrue($u->verifyPasswordRecoveryCode($em["code"]));
		$this->assertFalse($u->verifyPasswordRecoveryCode(""));
	}

	public function testValidateWebID(){
		$u = new User();
		$this->assertFalse($u->validateWebID(""));
		$this->assertTrue($u->validateWebID("abc123ABC__-~@\$"));
		$this->assertFalse($u->validateWebID("abc!A"));
		$this->assertFalse($u->validateWebID("abc%A"));
		$this->assertFalse($u->validateWebID("abc)A"));
		$this->assertFalse($u->validateWebID("abc(A"));
		$this->assertFalse($u->validateWebID("abc*A"));
		$this->assertFalse($u->validateWebID("abc&A"));
		$this->assertFalse($u->validateWebID("abc^A"));
		$this->assertFalse($u->validateWebID("abc`A"));
		$this->assertFalse($u->validateWebID("abc'A"));
		$this->assertFalse($u->validateWebID("abc\"A"));
		$this->assertFalse($u->validateWebID("abc#A"));
	}

	public function testSignUp(){
		$u = new User();
		$dal = new fakeDAL();
		$ret = $u->signup("new_user", "new_password", "myemail@mydomain.com", $dal, false);
		$this->assertEquals("Sign up success!", $ret);
		$this->assertEquals("new_user" ,$u->getUsername());
		$this->assertNotEquals("new_password", $u->getPasswd());
		$this->assertFalse($u->isEmailVerified());
		$this->assertEquals("myemail@mydomain.com", $u->getEmail());

		$dal = new fakeDAL();
		$ret = $u->signup("NEW_USER", "new_password", "myemail@mydomain.com", $dal, false);
		$this->assertEquals("Sign up success!", $ret);
		$this->assertEquals("new_user" ,$u->getUsername());
		$this->assertNotEquals("new_password", $u->getPasswd());
		$this->assertFalse($u->isEmailVerified());
		$this->assertEquals("myemail@mydomain.com", $u->getEmail());
	}

	public function testSignUpDuplicate(){
		$u = new User();
		$dal = new fakeDAL();
		$ret = $u->signup("new_user", "new_password", "myemail@mydomain.com", $dal, false);
		$this->assertEquals("Sign up success!", $ret);
		$this->assertEquals("new_user" ,$u->getUsername());
		$this->assertNotEquals("new_password", $u->getPasswd());
		$this->assertFalse($u->isEmailVerified());
		$this->assertEquals("myemail@mydomain.com", $u->getEmail());

		$ret = $u->signup("NEW_USER", "new_password", "myemail@mydomain.com", $dal, false);
		$this->assertEquals("Sign up failed:\nUsername or email already in use!", $ret);
	}

	public function testGettersAndSetters(){
		$u = new User();
		$u->setEmailVerificationCodes(["ABC"]);
		$this->assertEquals(["ABC"], $u->getEmailVerificationCodes());

		$u->setPasswordRecoveryCodes(["ABC"]);
		$this->assertEquals(["ABC"], $u->getPasswordRecoveryCodes());

		$this->assertTrue($u->validateName("Michael"));
		$this->assertTrue($u->validateName("Michael-"));
		$this->assertTrue($u->validateName("Michael-D"));
		$this->assertTrue($u->validateName("Michael\$"));
		$this->assertFalse($u->validateName("\"Michael\""));
		$this->assertFalse($u->validateName("'Michael'"));
		$this->assertTrue($u->validateName("`Michael`"));

		$u->setUserID("1");
		$this->assertEquals("1", $u->getUserID());

		$u->setUsername("MICHAEL");
		$this->assertEquals("michael", $u->getUsername());
		$u->setUsername("michael");
		$this->assertEquals("michael", $u->getUsername());

		$u->setFname("Michael");
		$this->assertEquals("Michael", $u->getFname());
		$u->setLname("Michael");
		$this->assertEquals("Michael", $u->getLname());

		$this->assertEquals(1, $u->getGender());
		$u->setGender(2);
		$this->assertEquals(2, $u->getGender());

		$u->setPasswdDB("ABC");
		$this->assertEquals("ABC", $u->getPasswd());

		$u->setFeedText("ABC");
		$this->assertEquals("ABC", $u->getFeedText());

		$u->setFeedLength(0);
		$this->assertEquals(0, $u->getFeedLength());

		$this->assertFalse($u->isPrivateFeed());
		$u->setPrivateFeed(true);
		$this->assertTrue($u->isPrivateFeed());
		$u->setPrivateFeed(false);
		$this->assertFalse($u->isPrivateFeed());

		$u->setFeedDetails([]);
		$this->assertEquals([], $u->getFeedDetails());
	}
}

class fakeDAL extends \AudioDidact\DB\DAL{
	private $user;

	/**
	 * Returns User class built from the database
	 *
	 * @param string $username
	 * @return User
	 */
	public function getUserByUsername($username){
		return $this->user;
	}

	/**
	 * Returns User class built from the database
	 *
	 * @param string $email
	 * @return User
	 */
	public function getUserByEmail($email){
		// TODO: Implement getUserByEmail() method.
	}

	/**
	 * Returns User class built from the database
	 *
	 * @param int $id
	 * @return User
	 */
	public function getUserByID($id){
		// TODO: Implement getUserByID() method.
	}

	/**
	 * Returns User class built from the database
	 *
	 * @param string $webID
	 * @return User
	 */
	public function getUserByWebID($webID){
		// TODO: Implement getUserByWebID() method.
	}

	/**
	 * Gets all the videos from the database in the user's current feed
	 * limited by the max number of items the user has set
	 *
	 * @param User $user
	 * @return mixed
	 */
	public function getFeed(User $user){
		// TODO: Implement getFeed() method.
	}

	/**
	 * Gets all the videos from the database
	 *
	 * @param User $user
	 * @return mixed
	 */
	public function getFullFeedHistory(User $user){
		// TODO: Implement getFullFeedHistory() method.
	}

	/**
	 * Gets the full text of the feed from the database
	 *
	 * @param User $user
	 * @return string
	 */
	public function getFeedText(User $user){
		// TODO: Implement getFeedText() method.
	}

	/**
	 * Puts user into the database
	 *
	 * @param User $user
	 * @return void
	 */
	public function addUser(User $user){
		$this->user = $user;
	}

	/**
	 * Adds video into the video database for a specific user
	 *
	 * @param \AudioDidact\Video $vid
	 * @param User $user
	 * @return mixed
	 */
	public function addVideo(\AudioDidact\Video $vid, User $user){
		// TODO: Implement addVideo() method.
	}

	/**
	 * Updates an existing video in the video database for a specific user
	 *
	 * @param \AudioDidact\Video $vid
	 * @param User $user
	 * @return mixed
	 */
	public function updateVideo(\AudioDidact\Video $vid, User $user){
		// TODO: Implement updateVideo() method.
	}

	/**
	 * Sets feed xml text for a user
	 *
	 * @param User $user
	 * @param $feed
	 * @return mixed
	 */
	public function setFeedText(User $user, $feed){
		// TODO: Implement setFeedText() method.
	}

	/**
	 * Updates user entry in the database
	 *
	 * @param User $user
	 */
	public function updateUser(User $user){
		// TODO: Implement updateUser() method.
	}

	/**
	 * Updates only a user's password in the database
	 *
	 * @param User $user
	 */
	public function updateUserPassword(User $user){
		// TODO: Implement updateUserPassword() method.
	}

	/**
	 * Updates only a user's email verification and password recovery codes in the database
	 *
	 * @param User $user
	 */
	public function updateUserEmailPasswordCodes(User $user){
		// TODO: Implement updateUserEmailPasswordCodes() method.
	}

	/**
	 * Sets up any database necessary
	 *
	 * @param int $code
	 * @return mixed
	 */
	public function makeDB($code){
		// TODO: Implement makeDB() method.
	}

	/**
	 * Verifies the database
	 *
	 * @return mixed
	 */
	public function verifyDB(){
		// TODO: Implement verifyDB() method.
	}

	/**
	 * Returns an array of video IDs that can be safely deleted
	 *
	 * @return mixed
	 */
	public function getPrunableVideos() {
 // TODO: Implement getPrunableVideos() method.
}}
