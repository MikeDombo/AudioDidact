<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 8/13/2017
 * Time: 8:09 PM
 */

use AudioDidact\Video;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase {
	public function testConstructor(){
		$v = new Video();
		$this->assertNotNull($v);
	}

	public function testGettersAndSetters(){
		$v = new Video();

		$v->setId(111);
		$this->assertEquals(111, $v->getId());

		$v->setTime(123456789);
		$this->assertEquals(123456789, $v->getTime());

		$v->setOrder(0);
		$this->assertEquals(0, $v->getOrder());

		$v->setAuthor("Michael");
		$this->assertEquals("Michael", $v->getAuthor());

		$v->setDesc("Description");
		$this->assertEquals("Description", $v->getDesc());

		$v->setDuration(0);
		$this->assertEquals(0, $v->getDuration());
		$v->setDuration(10);
		$this->assertEquals(10, $v->getDuration());

		$v->setTitle("");
		$this->assertEquals("", $v->getTitle());

		$v->setFilename("ABC");
		$this->assertEquals("ABC", $v->getFilename());

		$v->setThumbnailFilename("thumb.jpg");
		$this->assertEquals("thumb.jpg", $v->getThumbnailFilename());

		$v->setURL("");
		$this->assertEquals("", $v->getURL());

		$v->setIsVideo(false);
		$v->setFilename("ABC");
		$this->assertFalse($v->isIsVideo());
		$this->assertEquals(".mp3", $v->getFileExtension());
		$v->setIsVideo(true);
		$v->setFilename("ABC");
		$this->assertTrue($v->isIsVideo());
		$this->assertEquals(".mp4", $v->getFileExtension());
	}
}
