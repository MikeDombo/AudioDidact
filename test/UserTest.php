<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 5/29/2017
 * Time: 7:42 PM
 */

require_once __DIR__."/../header.php";

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
		$this->assertInternalType('string', $em["code"]);
		$this->assertInternalType('int', $em["expiration"]);
		$this->assertTrue($u->verifyEmailVerificationCode($em["code"]));
		$this->assertFalse($u->verifyEmailVerificationCode(""));

		$em = $u->addPasswordRecoveryCode();
		$gotEmail = $u->getPasswordRecoveryCodes();
		$this->assertCount(1, $gotEmail);
		$this->assertTrue($em == $gotEmail[0]);
		$this->assertArrayHasKey("code", $em);
		$this->assertArrayHasKey("expiration", $em);
		$this->assertInternalType('string', $em["code"]);
		$this->assertInternalType('int', $em["expiration"]);
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
}
