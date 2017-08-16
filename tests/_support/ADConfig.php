<?php

namespace Codeception\Module;
chdir(__DIR__ . "/../../src/");

class ADConfig extends \Codeception\Module {
	public function _beforeSuite($settings = array()){
		@unlink("database.sqlite");
		rename("config.yml", "config-bak.yml");
		copy(__DIR__ . "/../config-sqlite-testing.yml", "config.yml");
	}

	public function _afterSuite(){
		@unlink("database.sqlite");
		rename("config-bak.yml", "config.yml");
	}
}
