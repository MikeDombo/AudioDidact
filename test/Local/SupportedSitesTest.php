<?php
require_once __DIR__ . "/../../src/header.php";
chdir(__DIR__ . "/../../src/");

use \AudioDidact\SupportedSites\YouTube;
use \AudioDidact\SupportedSites\Vimeo;
use \AudioDidact\SupportedSites\SoundCloud;
use \AudioDidact\SupportedSites\CRTV;
use \PHPUnit\Framework\TestCase;

/**
 * @covers AudioDidact\SupportedSites\YouTube
 * @covers AudioDidact\SupportedSites\Vimeo
 * @covers AudioDidact\SupportedSites\SoundCloud
 * @covers AudioDidact\SupportedSites\CRTV
 * Class SupportedSitesTest
 */
class SupportedSitesTest extends TestCase {
	public function testYouTubeSupportsURL(){
		$this->assertTrue(YouTube::supportsURL("https://www.youtube.com/watch?v=0cJqiO_Q0KA"));
		$this->assertTrue(YouTube::supportsURL("https://youtu.be/0cJqiO_Q0KA"));
		$this->assertFalse(YouTube::supportsURL("something else"));
		$this->assertFalse(YouTube::supportsURL("somethingelse"));
		$this->assertFalse(YouTube::supportsURL("something.else"));
	}

	public function testVimeoSupportsURL(){
		$this->assertTrue(Vimeo::supportsURL("https://vimeo.com/228614940"));
		$this->assertFalse(Vimeo::supportsURL("https://youtu.be/0cJqiO_Q0KA"));
		$this->assertFalse(Vimeo::supportsURL("something else"));
		$this->assertFalse(Vimeo::supportsURL("somethingelse"));
		$this->assertFalse(Vimeo::supportsURL("something.else"));
	}

	public function testSoundCloudSupportsURL(){
		$this->assertTrue(SoundCloud::supportsURL("https://soundcloud.com/ravishouse/andy-gruhin-bring-me-down-ravi"));
		$this->assertFalse(SoundCloud::supportsURL("https://youtu.be/0cJqiO_Q0KA"));
		$this->assertFalse(SoundCloud::supportsURL("something else"));
		$this->assertFalse(SoundCloud::supportsURL("somethingelse"));
		$this->assertFalse(SoundCloud::supportsURL("something.else"));
	}

	public function testCRTVSupportsURL(){
		if(empty(SUPPORTED_SITES_CRTV)){
			$this->assertFalse(CRTV::supportsURL("https://www.crtv.com/video/295-arm-all-the-teachers-chuck-woolery-guests--louder-with-crowder"));
		}
		else{
			$this->assertTrue(CRTV::supportsURL("https://www.crtv.com/video/295-arm-all-the-teachers-chuck-woolery-guests--louder-with-crowder"));
		}
		$this->assertFalse(CRTV::supportsURL("https://youtu.be/0cJqiO_Q0KA"));
		$this->assertFalse(CRTV::supportsURL("something else"));
		$this->assertFalse(CRTV::supportsURL("somethingelse"));
		$this->assertFalse(CRTV::supportsURL("something.else"));
	}
}

