<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 5/29/2017
 * Time: 7:42 PM
 */

require_once __DIR__ . "/../../src/header.php";
chdir(__DIR__ . "/../../src/");

use AudioDidact\SupportedSites\Vimeo;
use \PHPUnit\Framework\TestCase;

/**
 * @covers AudioDidact\SupportedSites\Vimeo
 * @covers AudioDidact\SupportedSites\SupportedSite
 * Class YouTubeTest
 */
class VimeoTest extends TestCase{
	public function testConstructor(){
		$v1 = new Vimeo("https://vimeo.com/228614940", false);
		$v2 = new Vimeo("https://vimeo.com/228614940", true);

		$this->assertInstanceOf(Vimeo::class, $v1);
		$this->assertInstanceOf(Vimeo::class, $v2);

		$this->assertEquals("228614940", $v1->getVideo()->getId());
		$this->assertEquals("228614940", $v2->getVideo()->getId());
		$this->assertFalse($v1->getVideo()->isIsVideo());
		$this->assertTrue($v2->getVideo()->isIsVideo());

		$this->assertEquals("LAMAR+NIK", $v1->getVideo()->getAuthor());
		$this->assertContains("*2018 SXSW Official Selection [Music Videos]\n*2017 UKMVA Nomination [Best Indie/Rock Newcomer]\n*2017 IMVF Nomination [Best Animation]", $v1->getVideo()->getDesc());
		$this->assertEquals("THE SHINS “HALF A MILLION” [DIR. LAMAR+NIK]", $v1->getVideo()->getTitle());
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Could not parse that vimeo URL
	 */
	public function testBadURL(){
		$download = new Vimeo("https://vimeo.com/", false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Could not parse that vimeo URL
	 */
	public function testBadID(){
		$download = new Vimeo("https://vimeo.com/12sd5gd", false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Private video or some other parse error
	 */
	public function testPrivateVideo(){
		$download = new Vimeo("https://vimeo.com/228614943", false);
	}

	public function testDownload(){
		$download = new Vimeo("https://vimeo.com/228614940", false);
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
		$this->assertEquals(203, $video->getDuration());

		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().".mp4");
		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());

		$download = new Vimeo("https://vimeo.com/228614940", true);
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
		$this->assertEquals(203, $video->getDuration());

		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getFilename().$video->getFileExtension());
		unlink(getcwd()."/".DOWNLOAD_PATH."/".$video->getThumbnailFilename());
	}
}