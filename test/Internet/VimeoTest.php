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
		$this->assertStringContainsString("lamarnik", $v1->getVideo()->getDesc());
		$this->assertEquals("THE SHINS | HALF A MILLION", $v1->getVideo()->getTitle());
	}

	public function testBadURL(){
	    $this->expectExceptionMessage("Could not parse that vimeo URL");
		new Vimeo("https://vimeo.com/", false);
	}

	public function testBadID(){
        $this->expectExceptionMessage("Could not parse that vimeo URL");
		new Vimeo("https://vimeo.com/12sd5gd", false);
	}

	public function testPrivateVideo(){
        $this->expectExceptionMessage("Private video or some other parse error");
		new Vimeo("https://vimeo.com/228614943", false);
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
