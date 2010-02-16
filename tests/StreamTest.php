<?php

class Facade_StreamTest extends UnitTestCase
{
	public function testReadingAFileStream()
	{
		$fp = tmpfile();
		fwrite($fp,'test');
		rewind($fp);

		$stream = new Facade_Stream($fp, 4, false);
		$this->assertFalse($stream->isEof());
		$this->assertEqual($stream->read(2), 'te');
		$this->assertEqual($stream->read(), 'st');
		$this->assertEqual($stream->getOffset(), 4);
		$this->assertEqual($stream->getLength(), 4);
		$this->assertTrue($stream->isEof());
	}

	public function testWritingAFileStream()
	{
		$fp = tmpfile();

		$stream = new Facade_Stream($fp, null, true);
		$this->assertEqual($stream->write('test'), 4);
		$this->assertEqual($stream->getOffset(), 4);
		$this->assertEqual($stream->getLength(), null);
		$this->assertEqual($stream->rewind(), null);
		$this->assertEqual($stream->getOffset(), 0);
		$this->assertEqual($stream->toString(), 'test');
	}

	public function testReadingAStringStream()
	{
		$stream = Facade_Stream::fromString('test');

		$this->assertFalse($stream->isEof());
		$this->assertEqual($stream->read(2), 'te');
		$this->assertEqual($stream->read(), 'st');
		$this->assertEqual($stream->getOffset(), 4);
		$this->assertEqual($stream->getLength(), 4);
		$this->assertTrue($stream->isEof());

		$this->expectException();
		$stream->write('blargh');
	}

	public function testCopyingAStream()
	{
		$s1 = new Facade_Stream(tmpfile(),null,true);
		$s2 = Facade_Stream::fromString('llamas');

		$s1->copy($s2);
		$s1->rewind();

		$this->assertEqual($s1->toString(), 'llamas');
	}
}
