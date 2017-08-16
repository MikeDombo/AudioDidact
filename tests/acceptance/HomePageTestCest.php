<?php



class HomePageTestCest {
	public function _before(AcceptanceTester $I){
	}

	public function _after(AcceptanceTester $I){
	}

	public function frontpageWorks(AcceptanceTester $I){
		$I->wantTo("Test the homepage");
		$I->amOnPage('/');
		$I->see('Custom Podcast Service');
		$I->see('Sign up for an account');
		$I->see('Sign Up!');
		$I->see('Login');
	}

	public function signUp(AcceptanceTester $I){
		$I->wantTo("Sign up for an account");
		$I->amOnPage('/');
		$I->click("Sign Up!");
		$I->seeInCurrentUrl("signup");
		$I->seeInTitle("Sign Up for AudioDidact");
		$I->fillField("#email", "michael@mikedombrowski.com");
		$I->fillField("#unameSignup", "michael-tester");
		$I->fillField("#passwdSignup", "michael-tester");
		$I->click("Sign Up");
		$I->waitForText('Logout', 30);
		$I->seeInCurrentUrl("faq");
		$I->click("Home");
		$I->see("Before using AudioDidact you must verify your email address.");
	}

	public function signIn(AcceptanceTester $I){
		$I->wantTo("Sign in with my new account");
		$I->amOnPage('/');
		$I->see("Login");
		$I->click("Login");
		$I->fillField("#uname", "michael-tester");
		$I->fillField("#passwd", "michael-tester");
		$I->pressKey("#passwd", WebDriverKeys::ENTER);
		$I->waitForText('Logout', 30);
		$I->seeInTitle("Home | AudioDidact");
		$I->see("Before using AudioDidact you must verify your email address.");
	}
}
