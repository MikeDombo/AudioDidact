<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 6/2/2017
 * Time: 7:31 PM
 */


require_once __DIR__."/../src/header.php";
chdir(__DIR__."/../src/");
use PHPUnit\Framework\TestCase;
use AudioDidact\SupportedSites\SoundCloud;

/**
 * @covers AudioDidact\SupportedSites\SoundCloud
 * Class SoundCloudTest
 */
class SoundCloudTest extends TestCase{
	private $testUrl = "https://soundcloud.com/ravishouse/andy-gruhin-bring-me-down-ravi";

	public function testConstructor(){
		$sc = new SoundCloud($this->testUrl, false);
		$this->assertEquals($sc->getVideo()->getURL(), $this->testUrl);
		$this->assertFalse($sc->getVideo()->isIsVideo());
		$sc = new SoundCloud($this->testUrl, true);
		$this->assertEquals($sc->getVideo()->getURL(), $this->testUrl);
		$this->assertFalse($sc->getVideo()->isIsVideo());
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Invalid SoundCloud URL Entered.
	 */
	public function testNonSoundCloudURL(){
		$sc = new SoundCloud("http://youtube.com/watch?v=12345678911", false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Invalid SoundCloud URL Entered.
	 */
	public function testBadURL(){
		$sc = new SoundCloud("http://soundcloud.com/ravishouse/", false);
	}

	public function testDownload(){
		$sc = new SoundCloud($this->testUrl, false);
		$video = $sc->getVideo();

		// Cleanup in case a previous test run failed before deleting downloaded files
		@unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		@unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());

		if(!$sc->allDownloaded()){
			$sc->downloadVideo();
			$sc->downloadThumbnail();
			if(!$video->isIsVideo()){
				$sc->convert();
			}
		}

		$this->assertFileExists(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		$this->assertFileNotExists(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().".mp4");
		$this->assertFileExists(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());
		$this->assertEquals(52, $video->getDuration());

		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());
	}

}
