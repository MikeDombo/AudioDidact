<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 5/29/2017
 * Time: 7:42 PM
 */

require_once __DIR__ . "/../../src/header.php";
chdir(__DIR__ . "/../../src/");

use AudioDidact\SupportedSites\YouTube;
use \PHPUnit\Framework\TestCase;

/**
 * @covers AudioDidact\SupportedSites\YouTube
 * Class YouTubeTest
 */
class YouTubeTest extends TestCase{
	public function testConstructor(){
		$yt1 = new YouTube("https://www.youtube.com/watch?v=oAqEAaSkOhQ" ,true);
		$yt2 = new YouTube("http://www.youtube.com/watch?v=oAqEAaSkOhQ" ,true);
		$yt3 = new YouTube("https://youtube.com/watch?v=oAqEAaSkOhQ" ,true);
		$yt4 = new YouTube("http://youtube.com/watch?v=oAqEAaSkOhQ" ,true);
		$yt5 = new YouTube("https://youtu.be/oAqEAaSkOhQ" ,true);
		$yt6 = new YouTube("http://youtu.be/oAqEAaSkOhQ" ,true);
		$yt7 = new YouTube("https://www.youtube.com/watch?v=oAqEAaSkOhQ" ,false);
		$yt8 = new YouTube("http://www.youtube.com/watch?v=oAqEAaSkOhQ" ,false);
		$yt9 = new YouTube("https://youtube.com/watch?v=oAqEAaSkOhQ" ,false);
		$yt10 = new YouTube("http://youtube.com/watch?v=oAqEAaSkOhQ" ,false);
		$yt11 = new YouTube("https://youtu.be/oAqEAaSkOhQ" ,false);
		$yt12 = new YouTube("http://youtu.be/oAqEAaSkOhQ" ,false);

		$this->assertEquals("oAqEAaSkOhQ", $yt1->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt2->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt3->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt4->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt5->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt6->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt7->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt8->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt9->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt10->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt11->getVideo()->getId());
		$this->assertEquals("oAqEAaSkOhQ", $yt12->getVideo()->getId());

		$this->assertEquals("President Trump's Trip Abroad: Rome, Vatican City, & Brussels", $yt1->getVideo()
			->getTitle());
		$this->assertEquals("The White House", $yt1->getVideo()->getAuthor());
		$this->assertTrue($yt1->getVideo()->isIsVideo());
		$this->assertFalse($yt7->getVideo()->isIsVideo());
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage ID Inaccessible
	 */
	public function testBadID1(){
		$yt1 = new YouTube("aaaaaaaaaaa" ,true);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage ID Inaccessible
	 */
	public function testBadID2(){
		$yt1 = new YouTube("aaaaaaa" ,true);
		$yt1 = new YouTube("aaaaaaaaaaa" ,false);
		$yt1 = new YouTube("aaaaaaa" ,false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage ID Inaccessible
	 */
	public function testBadID3(){
		$yt1 = new YouTube("aaaaaaaaaaa" ,false);
		$yt1 = new YouTube("aaaaaaa" ,false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage ID Inaccessible
	 */
	public function testBadID4(){
		$yt1 = new YouTube("aaaaaaa" ,false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage URL is a playlist. AudioDidact does not currently support playlists
	 */
	public function testPlaylist(){
		new YouTube("https://www.youtube.com/playlist?list=PL96C35uN7xGK_y459BdHCtGeftqs5_nff" ,false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage URL is a channel
	 */
	public function testChannel1(){
		new YouTube("https://www.youtube.com/user/enyay" ,false);
		new YouTube("https://www.youtube.com/c/ted" ,false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage URL is a channel
	 */
	public function testChannel2(){
		new YouTube("https://www.youtube.com/channel/UCCBVCTuk6uJrN3iFV_3vurg" ,false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage URL is a channel
	 */
	public function testChannel3(){
		new YouTube("https://www.youtube.com/c/ted" ,false);
	}

	public function testDownload(){
		$download = new YouTube("oAqEAaSkOhQ", false);
		$video = $download->getVideo();

		// Cleanup in case a previous test run failed before deleting downlaoded files
		@unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		@unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().".mp4");
		@unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());

		if(!$download->allDownloaded()){
			$download->downloadVideo();
			$download->downloadThumbnail();
			if(!$video->isIsVideo()){
				$download->convert();
				$download->applyArt();
			}
		}

		$this->assertFileExists(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		$this->assertFileExists(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().".mp4");
		$this->assertFileExists(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());
		$this->assertEquals(52, $video->getDuration());

		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().".mp4");
		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());

		$download = new YouTube("oAqEAaSkOhQ", true);
		$video = $download->getVideo();
		if(!$download->allDownloaded()){
			$download->downloadVideo();
			$download->downloadThumbnail();
			if(!$video->isIsVideo()){
				$download->convert();
				$download->applyArt();
			}
		}

		$this->assertFileExists(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		$this->assertFileNotExists(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().".mp3");
		$this->assertFileExists(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());
		$this->assertEquals(52, $video->getDuration());

		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());
	}
}
