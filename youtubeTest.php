<?php
use phpunit\framework\TestCase;
require_once "youtube.php";

/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 6/6/2016
 * Time: 9:36 PM
 */
class youtubeTest extends TestCase{

	public function testInitEmpty(){
		$yt = new youtube("");
		$this->assertEquals("", $yt->getVideoID());
		@rmdir($yt->getDownloadPath());
		$yt = new youtube("");
		$this->assertEquals("", $yt->getVideoID());

		$this->assertEquals("feed.csv", $yt->getCSVFilePath());
		return 1;
	}

	public function testInitNull(){
		$yt = new youtube();
		$this->assertEquals("", $yt->getVideoID());
		return 1;
	}

	public function testInitID(){
		$yt = new youtube("fA4TIG6qcUM");
		$this->assertEquals("fA4TIG6qcUM", $yt->getVideoID());
		$this->assertEquals("The Amazing Atheist", $yt->getVideoAuthor());
		$this->assertTrue($yt->getVideoTime() > 100);
		$this->assertEquals("It Will be Gone Forever!", $yt->getVideoTitle());
		$this->assertTrue(strlen($yt->getDescr())>5);
		return 1;
	}

	public function testInitURL(){
		$yt = new youtube("https://www.youtube.com/watch?v=fA4TIG6qcUM");
		$this->assertEquals("fA4TIG6qcUM", $yt->getVideoID());
		return 1;
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testInitBadID(){
		new youtube("https://www.youtube.com/watch?v=iaMkvBr6908");
	}

	public function testInitBe(){
		$yt = new youtube("https://youtu.be/fA4TIG6qcUM");
		$this->assertEquals("fA4TIG6qcUM", $yt->getVideoID());
		return 1;
	}
	/**
	 * @depends testInitURL
	 */
	public function testDownloadThumb(){
		$yt = new youtube("https://www.youtube.com/watch?v=fA4TIG6qcUM");
		$this->assertFalse($yt->allDownloaded(), "Nothing should be downloaded to begin with");
		$this->assertFileExists($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".jpg", "Thumbnail 
		wasn't downloaded");
		$this->assertTrue(filesize($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".jpg")>10,
			"Thumbnail file was created, but is has no size");
		unlink($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".jpg");
		return 1;
	}

	/**
	 * @depends testInitURL
	 */
	public function testInCSV(){
		$yt = new youtube("https://www.youtube.com/watch?v=fA4TIG6qcUM");
		$this->assertFalse($yt->isInCSV());
		@unlink($yt->getCSVFilePath());
		return 1;
	}

	/**
	 * @depends testInCSV
	 */
	public function testAddToCSV(){
		$yt = new youtube("https://www.youtube.com/watch?v=fA4TIG6qcUM");
		$this->assertFalse($yt->isInCSV());
		$yt->addToCSV();
		$this->assertTrue($yt->isInCSV());
		@unlink($yt->getCSVFilePath());
		return 1;
	}

	/**
	 * @depends testInitURL
	 * @depends testDownloadThumb
	 */
	public function testDownloadVideo(){
		$yt = new youtube("https://www.youtube.com/watch?v=fA4TIG6qcUM");
		$this->assertNull($yt->downloadVideo());
		$this->assertFileExists($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp4", "Video wasn't 
		downloaded");
		$this->assertTrue(filesize($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp4")>10, "Video 
		didn't download properly");
		unlink($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp4");
		return 1;
	}

	/**
	 * @depends testDownloadVideo
	 */
	public function testConvertVideo(){
		$yt = new youtube("https://www.youtube.com/watch?v=fA4TIG6qcUM");
		$yt->downloadVideo();
		$this->assertTrue($yt->allDownloaded());
		$this->assertFileExists($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp3", "Conversion 
		never started");
		$this->assertTrue(filesize($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp3")>10,
			"Converted file is not a proper size");
		@unlink($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp4");
		@unlink($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp3");
		return 1;
	}

	/**
	 * @depends testConvertVideo
	 */
	public function testInitInstant(){
		$yt = new youtube("fA4TIG6qcUM", true);
		$this->assertFileExists($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".jpg", "Thumbnail 
		wasn't downloaded");
		$this->assertTrue(filesize($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".jpg")>10,
			"Thumbnail file was created, but is has no size");
		$this->assertFileExists($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp4", "Video wasn't 
		downloaded");
		$this->assertTrue(filesize($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp4")>10, "Video 
		didn't download properly");
		$this->assertFileExists($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp3", "Conversion 
		never started");
		$this->assertTrue(filesize($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp3")>10,
			"Converted file is not a proper size");
		return 1;
	}

	/**
	 * @depends testInitInstant
	 */
	public function testMakeFeed(){
		$yt = new youtube("fA4TIG6qcUM");
		$yt->addToCSV();
		$yt->deleteLast($yt->getCSVFilePath());
		$this->assertNotNull($yt->makeFullFeed());
		@unlink($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".jpg");
		@unlink($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp4");
		@unlink($yt->getDownloadPath().DIRECTORY_SEPARATOR.$yt->getVideoID().".mp3");
		@rmdir($yt->getDownloadPath());
		@unlink($yt->getCSVFilePath());
		@unlink($yt->getRssPath());
		return 1;
	}
}
