<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 8/13/2017
 * Time: 8:20 PM
 */

use AudioDidact\User;
use PHPUnit\Framework\TestCase;
use AudioDidact\GlobalFunctions;

require_once __DIR__ . "/../../src/header.php";
chdir(__DIR__ . "/../../src/");

/**
 * @covers \AudioDidact\GlobalFunctions
 * Class UtilitiesTest
 */
class UtilitiesTest extends TestCase {
	public function testPluralize(){
		$this->pluralizeTestHelper("word", "words");
		$this->pluralizeTestHelper("battery", "batteries");
		$this->pluralizeTestHelper("test", "tests");
		$this->pluralizeTestHelper("cache", "caches");
		$this->pluralizeTestHelper("potato", "potatoes");
		$this->pluralizeTestHelper("box", "boxes");
		$this->pluralizeTestHelper("class", "classes");
		$this->pluralizeTestHelper("branch", "branches");
		$this->pluralizeTestHelper("brush", "brushes");
	}

	public function testStringListicle(){
		$this->assertEquals("", GlobalFunctions::arrayToCommaSeparatedString([]));
		$this->assertEquals("one", GlobalFunctions::arrayToCommaSeparatedString(["one"]));
		$this->assertEquals("one and two", GlobalFunctions::arrayToCommaSeparatedString(["one", "two"]));
		$this->assertEquals("one, two, and three", GlobalFunctions::arrayToCommaSeparatedString(["one", "two", "three"]));
		$this->assertEquals("one, two, three, and four", GlobalFunctions::arrayToCommaSeparatedString(["one", "two", "three", "four"]));
	}

	public function testSecondsToTime(){
		require_once __DIR__ . '/../../src/userPageGenerator.php';

		$output = GlobalFunctions::secondsToTime(0);
		$this->assertEquals([], $output);

		$output = GlobalFunctions::secondsToTime(1);
		$this->assertEquals(["second" => 1], $output);

		$output = GlobalFunctions::secondsToTime(60);
		$this->assertEquals(["minute" => 1], $output);

		$output = GlobalFunctions::secondsToTime(61);
		$this->assertEquals(["minute" => 1, "second" => 1], $output);

		$output = GlobalFunctions::secondsToTime(122);
		$this->assertEquals(["minute" => 2, "second" => 2], $output);

		$output = GlobalFunctions::secondsToTime(3600);
		$this->assertEquals(["hour" => 1], $output);

		$output = GlobalFunctions::secondsToTime(3601);
		$this->assertEquals(["hour" => 1, "second" => 1], $output);

		$output = GlobalFunctions::secondsToTime(3661);
		$this->assertEquals(["hour" => 1, "minute" => 1, "second" => 1], $output);

		$output = GlobalFunctions::secondsToTime(3660);
		$this->assertEquals(["hour" => 1, "minute" => 1], $output);

		$output = GlobalFunctions::secondsToTime(86400);
		$this->assertEquals(["day" => 1], $output);

		$output = GlobalFunctions::secondsToTime(86401);
		$this->assertEquals(["day" => 1, "second" => 1], $output);

		$output = GlobalFunctions::secondsToTime(86461);
		$this->assertEquals(["day" => 1, "minute" => 1, "second" => 1], $output);

		$output = GlobalFunctions::secondsToTime(86460);
		$this->assertEquals(["day" => 1, "minute" => 1], $output);

		$output = GlobalFunctions::secondsToTime(604800);
		$this->assertEquals(["week" => 1], $output);

		$output = GlobalFunctions::secondsToTime(31536000);
		$this->assertEquals(["year" => 1], $output);
	}

	public function testUserLoginLogout(){
		$this->assertFalse(GlobalFunctions::userIsLoggedIn());
		$u = new User();
		GlobalFunctions::userLogIn($u);
		$this->assertTrue(GlobalFunctions::userIsLoggedIn());
	}

	public function test_mb_str_split(){
		$this->assertEquals(["a", "b", "c"], GlobalFunctions::mb_str_split("abc"));
		$this->assertEquals(["a", "ㅃ", "ㅎ", "汉", "字", "漢", "字"], GlobalFunctions::mb_str_split("aㅃㅎ汉字漢字"));
	}

	public function testGetDAL(){
		$this->assertInstanceOf(\AudioDidact\DB\DAL::class, GlobalFunctions::getDAL());
	}

	public function testDeepSetDictionaryValues(){
		$dict = ["a" => ["b" => ["c" => 1]]];
		$this->assertEquals(1, $dict["a"]["b"]["c"]);
		$dict = GlobalFunctions::deepSetDictionaryValues($dict, ["a", "b", "c"], 2);
		$this->assertEquals(2, $dict["a"]["b"]["c"]);
		$dict = GlobalFunctions::deepSetDictionaryValues($dict, ["a", "b", "d"], 3);
		$this->assertEquals(3, $dict["a"]["b"]["d"]);
		$dict = GlobalFunctions::deepSetDictionaryValues($dict, ["a", "b", "e", "f"], 4);
		$this->assertEquals(4, $dict["a"]["b"]["e"]["f"]);
	}

	public function testRandomToken(){
		$this->assertEquals(64, strlen(GlobalFunctions::randomToken()));
		$this->assertNotEquals(GlobalFunctions::randomToken(), GlobalFunctions::randomToken());
	}

	public function testVerifySameOriginHeader(){
		$this->assertFalse(GlobalFunctions::verifySameOriginHeader());
		$_SERVER["HTTP_ORIGIN"] = "https://localhost:29/somegarbage/sdfu9w";
		$this->assertTrue(GlobalFunctions::verifySameOriginHeader());
		$_SERVER["HTTP_ORIGIN"] = "https://localhost/somegarbage/sdfu9w";
		$this->assertTrue(GlobalFunctions::verifySameOriginHeader());
		unset($_SERVER["HTTP_ORIGIN"]);
		$_SERVER["HTTP_REFERER"] = "https://localhost:29/somegarbage/sdfu9w";
		$this->assertTrue(GlobalFunctions::verifySameOriginHeader());
		$_SERVER["HTTP_REFERER"] = "https://localhost/somegarbage/sdfu9w";
		$this->assertTrue(GlobalFunctions::verifySameOriginHeader());
		unset($_SERVER["HTTP_REFERER"]);
	}

	public function fullVerifyCSRF(){
		$this->assertFalse(GlobalFunctions::fullVerifyCSRF());
		$_COOKIE["AD_CSRF"] = "123456789";
		$this->assertFalse(GlobalFunctions::fullVerifyCSRF());
		$_SERVER["HTTP_ORIGIN"] = "https://localhost/somegarbage/sdfu9w";
		$this->assertFalse(GlobalFunctions::fullVerifyCSRF());
		$_SERVER["REQUEST_METHOD"] = "GET";
		$this->assertFalse(GlobalFunctions::fullVerifyCSRF());
		$_GET["CSRF_TOKEN"] = "123456789";
		$this->assertTrue(GlobalFunctions::fullVerifyCSRF());
		$_GET["CSRF_TOKEN"] = "1234";
		$this->assertFalse(GlobalFunctions::fullVerifyCSRF());
		$_SERVER["REQUEST_METHOD"] = "POST";
		$this->assertFalse(GlobalFunctions::fullVerifyCSRF());
		$_POST["CSRF_TOKEN"] = "123456789";
		$this->assertTrue(GlobalFunctions::fullVerifyCSRF());
		$_POST["CSRF_TOKEN"] = "1234";
		$this->assertFalse(GlobalFunctions::fullVerifyCSRF());
	}

	private function pluralizeTestHelper($wordSingular, $wordPlural){
		$this->assertEquals($wordPlural, GlobalFunctions::pluralize($wordSingular, 0));
		$this->assertEquals($wordSingular, GlobalFunctions::pluralize($wordSingular, 1));
		$this->assertEquals($wordPlural, GlobalFunctions::pluralize($wordSingular, 2));
		$this->assertEquals($wordPlural, GlobalFunctions::pluralize($wordSingular, -1));
		$this->assertEquals($wordPlural, GlobalFunctions::pluralize($wordSingular, -2));
	}
}
