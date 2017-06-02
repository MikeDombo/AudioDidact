<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 5/29/2017
 * Time: 7:42 PM
 */

require_once __DIR__."/../header.php";

use AudioDidact\SupportedSites\YouTube;
use \PHPUnit\Framework\TestCase;

/**
 * @covers AudioDidact\SupportedSites\YouTube
 * Class YouTubeTest
 */
class YouTubeTest extends TestCase{
	public function testConstructor(){
		$dal = getDAL();
		$u = new \AudioDidact\User();
		$podtube = new \AudioDidact\PodTube($dal, $u);

		$yt1 = new YouTube("https://www.youtube.com/watch?v=oAqEAaSkOhQ" ,true, $podtube);
		$yt2 = new YouTube("http://www.youtube.com/watch?v=oAqEAaSkOhQ" ,true, $podtube);
		$yt3 = new YouTube("https://youtube.com/watch?v=oAqEAaSkOhQ" ,true, $podtube);
		$yt4 = new YouTube("http://youtube.com/watch?v=oAqEAaSkOhQ" ,true, $podtube);
		$yt5 = new YouTube("https://youtu.be/oAqEAaSkOhQ" ,true, $podtube);
		$yt6 = new YouTube("http://youtu.be/oAqEAaSkOhQ" ,true, $podtube);
		$yt7 = new YouTube("https://www.youtube.com/watch?v=oAqEAaSkOhQ" ,false, $podtube);
		$yt8 = new YouTube("http://www.youtube.com/watch?v=oAqEAaSkOhQ" ,false, $podtube);
		$yt9 = new YouTube("https://youtube.com/watch?v=oAqEAaSkOhQ" ,false, $podtube);
		$yt10 = new YouTube("http://youtube.com/watch?v=oAqEAaSkOhQ" ,false, $podtube);
		$yt11 = new YouTube("https://youtu.be/oAqEAaSkOhQ" ,false, $podtube);
		$yt12 = new YouTube("http://youtu.be/oAqEAaSkOhQ" ,false, $podtube);

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
	 * @expectedExceptionMessage Download Failed!
	 */
	public function testBadID(){
		$dal = getDAL();
		$u = new \AudioDidact\User();
		$podtube = new \AudioDidact\PodTube($dal, $u);

		$yt1 = new YouTube("aaaaaaaaaaa" ,true, $podtube);
		$yt1 = new YouTube("aaaaaaa" ,true, $podtube);
		$yt1 = new YouTube("aaaaaaaaaaa" ,false, $podtube);
		$yt1 = new YouTube("aaaaaaa" ,false, $podtube);

	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Cannot download playlist
	 */
	public function testPlaylist(){
		$dal = getDAL();
		$u = new \AudioDidact\User();
		$podtube = new \AudioDidact\PodTube($dal, $u);

		new YouTube("https://www.youtube.com/playlist?list=PL96C35uN7xGK_y459BdHCtGeftqs5_nff" ,false, $podtube);

	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Cannot download channel
	 */
	public function testChannel(){
		$dal = getDAL();
		$u = new \AudioDidact\User();
		$podtube = new \AudioDidact\PodTube($dal, $u);

		new YouTube("https://www.youtube.com/user/enyay" ,false, $podtube);
		new YouTube("https://www.youtube.com/channel/UCCBVCTuk6uJrN3iFV_3vurg" ,false, $podtube);
		new YouTube("https://www.youtube.com/c/ted" ,false, $podtube);

	}

	public function testDownload(){
		$dal = getDAL();
		$u = new \AudioDidact\User();
		$podtube = new \AudioDidact\PodTube($dal, $u);

		$download = new YouTube("oAqEAaSkOhQ", false, $podtube);
		$video = $download->getVideo();
		if(!$download->allDownloaded()){
			$download->downloadVideo();
			$download->downloadThumbnail();
			if(!$video->isIsVideo()){
				$download->convert();
			}
		}

		$this->assertFileExists(__DIR__."/../".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		$this->assertFileExists(__DIR__."/../".DOWNLOAD_PATH."/".$video->getFilename().".mp4");
		$this->assertFileExists(__DIR__."/../".DOWNLOAD_PATH."/".$video->getThumbnailFilename());
		$this->assertEquals(52, $video->getDuration());

		unlink(__DIR__."/../".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		unlink(__DIR__."/../".DOWNLOAD_PATH."/".$video->getFilename().".mp4");
		unlink(__DIR__."/../".DOWNLOAD_PATH."/".$video->getThumbnailFilename());

		$download = new YouTube("oAqEAaSkOhQ", true, $podtube);
		$video = $download->getVideo();
		if(!$download->allDownloaded()){
			$download->downloadVideo();
			$download->downloadThumbnail();
			if(!$video->isIsVideo()){
				$download->convert();
			}
		}

		$this->assertFileExists(__DIR__."/../".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		$this->assertFileNotExists(__DIR__."/../".DOWNLOAD_PATH."/".$video->getFilename().".mp3");
		$this->assertFileExists(__DIR__."/../".DOWNLOAD_PATH."/".$video->getThumbnailFilename());
		$this->assertEquals(52, $video->getDuration());

		unlink(__DIR__."/../".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		unlink(__DIR__."/../".DOWNLOAD_PATH."/".$video->getThumbnailFilename());
	}
}
