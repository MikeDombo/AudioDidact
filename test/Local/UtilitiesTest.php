<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 8/13/2017
 * Time: 8:20 PM
 */

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
	}

	public function testStringListicle(){
		$this->assertEquals("", GlobalFunctions::stringListicle([]));
		$this->assertEquals("one", GlobalFunctions::stringListicle(["one"]));
		$this->assertEquals("one and two", GlobalFunctions::stringListicle(["one", "two"]));
		$this->assertEquals("one, two, and three", GlobalFunctions::stringListicle(["one", "two", "three"]));
		$this->assertEquals("one, two, three, and four", GlobalFunctions::stringListicle(["one", "two", "three", "four"]));
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

	private function pluralizeTestHelper($wordSingular, $wordPlural){
		$this->assertEquals($wordPlural, GlobalFunctions::pluralize($wordSingular, 0));
		$this->assertEquals($wordSingular, GlobalFunctions::pluralize($wordSingular, 1));
		$this->assertEquals($wordPlural, GlobalFunctions::pluralize($wordSingular, 2));
		$this->assertEquals($wordPlural, GlobalFunctions::pluralize($wordSingular, -1));
		$this->assertEquals($wordPlural, GlobalFunctions::pluralize($wordSingular, -2));
	}
}
