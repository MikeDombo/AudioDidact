<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 8/13/2017
 * Time: 8:20 PM
 */

use PHPUnit\Framework\TestCase;

class UtilitiesTest extends TestCase {
	public function testPluralize(){
		require_once __DIR__ . '/../../src/header.php';

		$this->pluralizeTestHelper("word", "words");
		$this->pluralizeTestHelper("battery", "batteries");
		$this->pluralizeTestHelper("test", "tests");
		$this->pluralizeTestHelper("cache", "caches");
		$this->pluralizeTestHelper("potato", "potatoes");
	}

	public function testStringListicle(){
		$this->assertEquals("", stringListicle([]));
		$this->assertEquals("one", stringListicle(["one"]));
		$this->assertEquals("one and two", stringListicle(["one", "two"]));
		$this->assertEquals("one, two, and three", stringListicle(["one", "two", "three"]));
		$this->assertEquals("one, two, three, and four", stringListicle(["one", "two", "three", "four"]));
	}

	public function testSecondsToTime(){
		require_once __DIR__ . '/../../src/userPageGenerator.php';
		
		$output = secondsToTime(0);
		$this->assertEquals([], $output);

		$output = secondsToTime(1);
		$this->assertEquals(["second" => 1], $output);

		$output = secondsToTime(60);
		$this->assertEquals(["minute" => 1], $output);

		$output = secondsToTime(61);
		$this->assertEquals(["minute" => 1, "second" => 1], $output);

		$output = secondsToTime(122);
		$this->assertEquals(["minute" => 2, "second" => 2], $output);

		$output = secondsToTime(3600);
		$this->assertEquals(["hour" => 1], $output);

		$output = secondsToTime(3601);
		$this->assertEquals(["hour" => 1, "second" => 1], $output);

		$output = secondsToTime(3661);
		$this->assertEquals(["hour" => 1, "minute" => 1, "second" => 1], $output);

		$output = secondsToTime(3660);
		$this->assertEquals(["hour" => 1, "minute" => 1], $output);

		$output = secondsToTime(86400);
		$this->assertEquals(["day" => 1], $output);

		$output = secondsToTime(86401);
		$this->assertEquals(["day" => 1, "second" => 1], $output);

		$output = secondsToTime(86461);
		$this->assertEquals(["day" => 1, "minute" => 1, "second" => 1], $output);

		$output = secondsToTime(86460);
		$this->assertEquals(["day" => 1, "minute" => 1], $output);

		$output = secondsToTime(604800);
		$this->assertEquals(["week" => 1], $output);

		$output = secondsToTime(31536000);
		$this->assertEquals(["year" => 1], $output);
	}

	private function pluralizeTestHelper($wordSingular, $wordPlural){
		$this->assertEquals($wordPlural, pluralize($wordSingular, 0));
		$this->assertEquals($wordSingular, pluralize($wordSingular, 1));
		$this->assertEquals($wordPlural, pluralize($wordSingular, 2));
		$this->assertEquals($wordPlural, pluralize($wordSingular, -1));
		$this->assertEquals($wordPlural, pluralize($wordSingular, -2));
	}
}
