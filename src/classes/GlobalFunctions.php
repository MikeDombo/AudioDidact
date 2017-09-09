<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 8/16/2017
 * Time: 10:24 PM
 */

namespace AudioDidact;

require_once __DIR__ . '/../header.php';

class GlobalFunctions {

	/**
	 * Convert number of seconds into hours, minutes and seconds
	 * and return an array containing those values
	 *
	 * @param integer $inputSeconds Number of seconds to parse
	 * @return array
	 */
	public static function secondsToTime($inputSeconds){
		$conversion = ["second" => ["second" => 1],
			"minute" => ["second" => 60],
			"hour" => ["minute" => 60],
			"day" => ["hour" => 24],
			"week" => ["day" => 7],
			"month" => ["week" => 4],
			"year" => ["day" => 365]];

		return self::modularUnitExpansion($inputSeconds, $conversion);
	}

	public static function modularUnitExpansion($value, $conversionTable){
		$baseUnit = "";
		$newConversion = [];
		foreach($conversionTable as $unit => $convertArr){
			if(array_key_exists($unit, $convertArr) && $convertArr[$unit] == 1){
				$baseUnit = $unit;
			}
			foreach($convertArr as $conversionUnit => $conversionFactor){
				$conversionTable[$unit][$baseUnit] = $conversionTable[$conversionUnit][$baseUnit] * $conversionFactor;
				$newConversion[$unit] = $conversionTable[$conversionUnit][$baseUnit] * $conversionFactor;
			}
		}

		// Reverse sort so that the largest units are iterated through first
		arsort($newConversion);

		$remainingUnits = $value;
		$outputArray = [];
		foreach($newConversion as $unit => $conversionFactor){
			$val = intval(floor($remainingUnits / $conversionFactor));
			if($val > 0){
				$outputArray[$unit] = $val;
			}
			$remainingUnits = $remainingUnits % $conversionFactor;
		}

		return $outputArray;
	}

	/**
	 * Deletes all session variables and the session cookies
	 */
	public static function clearSession(){
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
		session_destroy();
		session_write_close();
	}

	/**
	 * Sets the CHECK_REQUIRED flag in the config file
	 *
	 * @param bool $checkRequired
	 */
	public static function setCheckRequired($checkRequired){
		$currentConfig = file_get_contents(__DIR__ . '/../config.php');
		$newConfig = preg_replace("/define\(\"CHECK_REQUIRED\",\s+(true|false)\)/", "define(\"CHECK_REQUIRED\", $checkRequired)", $currentConfig);
		file_put_contents(__DIR__ . '/../config.php', $newConfig);
	}

	public static function SRIChecksum($input){
		$hash = hash('sha256', $input, true);
		$hashBase64 = base64_encode($hash);

		return "sha256-$hashBase64";
	}

	public static function userLogIn(User $user){
		$_SESSION["loggedIn"] = true;
		$_SESSION["user"] = $user;
	}

	public static function userLogOut(){
		$_SESSION["loggedIn"] = false;
		unset($_SESSION["user"]);
		self::clearSession();
	}

	public static function userIsLoggedIn(){
		return (isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] && isset($_SESSION["user"]) && $_SESSION["user"] !=
			null);
	}

	/**
	 * Returns Pug (Jade) rendered HTML for a given view and options
	 *
	 * @param $view string Name of Pug view to be rendered
	 * @param $title string Title of the webpage
	 * @param array $options Additional options needed to render the view
	 * @param bool $prettyPrint If prettyPrint is false, all HTML is on a single line
	 * @return string Pug generated HTML
	 */
	public static function generatePug($view, $title, $options = [], $prettyPrint = false){
		$verified = true;

		$initialOptions = [
			'title' => $title,
			'subdir' => SUBDIR,
			'loggedIn' => "false",
			'localurl' => LOCAL_URL,
			'emailEnabled' => EMAIL_ENABLED
		];

		if(self::userIsLoggedIn()){
			$initialOptions["loggedIn"] = "true";
			/** @var User $user */
			$user = $_SESSION["user"];
			$userData = ["privateFeed" => $user->isPrivateFeed(), "fName" => $user->getFname(), "lName" => $user->getLname(),
				"gender" => $user->getGender(), "webID" => $user->getWebID(), "username" => $user->getUsername(),
				"email" => $user->getEmail(), "feedLength" => $user->getFeedLength(), "feedDetails" => $user->getFeedDetails()
			];
			if(!$user->isEmailVerified()){
				$verified = false;
			}

			$initialOptions["user"] = $userData;
		}

		$initialOptions["verified"] = $verified;

		// Allow overwriting keys, but log the problem
		$overWrittenKeys = array_intersect_key($initialOptions, $options);
		if(count($overWrittenKeys) > 0){
			error_log("You are overwriting " . count($overWrittenKeys) . " keys in the Pug options! "
				. implode(", ", array_keys($overWrittenKeys)));
		}

		$options = array_merge($initialOptions, $options);

		$pug = new \Pug\Pug(['prettyprint' => $prettyPrint]);

		return $pug->render($view, $options);
	}

	/**
	 * Returns the correct plural or singular form of the given word
	 *
	 * @param $word String singular form of the word
	 * @param $num int number of things the word is referring to
	 * @return string correct form of the given word for the input number
	 */
	public static function pluralize($word, $num){
		$vowels = ["a", "e", "i", "o", "u"];
		$lastCharExceptions = ["s", "o", "x"];
		$lastTwoCharExceptions = ["sh", "ch"];
		if($num == 1){
			return $word;
		}
		$lastChar = mb_substr($word, -1, 1);
		$lastTwoChars = mb_substr($word, -2, 2);
		if($lastChar == "y" && !in_array(mb_substr($word, -2, 1), $vowels, true)){
			return mb_substr($word, 0, mb_strlen($word) - 1) . "ies";
		}
		else if(in_array($lastChar, $lastCharExceptions, true)
			|| in_array($lastTwoChars, $lastTwoCharExceptions, true)){
			return $word . "es";
		}
		else{
			return $word . "s";
		}
	}

	public static function arrayToCommaSeparatedString($list){
		$frontOfList = array_slice($list, 0, -1);
		$lastElement = array_slice($list, -1, 1);

		$prependConjunction = "";
		if(count($frontOfList) > 0){
			$prependConjunction = " and ";
			if(count($frontOfList) > 1){
				$prependConjunction = "," . $prependConjunction;
			}
		}

		return implode(", ", $frontOfList) . $prependConjunction . (count($lastElement) == 0 ? "" : $lastElement[0]);
	}

	public static function mb_str_split($string){
		return preg_split('/(?<!^)(?!$)/u', $string);
	}

	/**
	 * Makes a new DAL class based on values in config.php
	 *
	 * @return \AudioDidact\DB\DAL
	 */
	public static function getDAL(){
		$myDalClass = ChosenDAL;

		return new $myDalClass(PDO_STR);
	}

	/**
	 * Sets the value of a dictionary subkey to $value.
	 *
	 * @param $dict array the dictionary that will have it's subkey set to $value.
	 * @param $keyHierarchy array the array of keys to set. To set something in the form of $array["a"]["b"]["c"],
	 * set $keyHierarchy to ["a","b","c"]
	 * @param $value mixed the value that will be added to the dictionary
	 * @return mixed the dictionary with the new value set
	 */
	public static function deepSetDictionaryValues($dict, $keyHierarchy, $value){
		$o = &$dict;
		for($i = 0; $i < count($keyHierarchy) - 1; $i++){
			$subKeyAtI = $keyHierarchy[$i];
			if(array_key_exists($subKeyAtI, $o)){
				$o = &$o[$subKeyAtI];
			}
			else{
				$o[$subKeyAtI] = [];
				$o = &$o[$subKeyAtI];
			}
		}

		$o[$keyHierarchy[count($keyHierarchy) - 1]] = $value;

		return $dict;
	}
}
