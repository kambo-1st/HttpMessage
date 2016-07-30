<?php
namespace Test;

// \Spl
use ReflectionClass;
use RuntimeException;

// \Http\Message
use Kambo\Http\Message\Stream;

// \vfs
use org\bovigo\vfs\vfsStream;

/**
 * Unit test for the Stream object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Virtual stream for the testing.
     *
     * @var vfsStream
     */
    private $root;

    /**
     * Setting virtual stream for the testing.
     *
     * @return void
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
    }

    /**
     * Test creating stream from file.
     *
     * @return void
     */
    public function testStreamCreate()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));

        $this->assertEquals($fileContent, $testStream->getContents());
    }

    /**
     * Test detaching stream - setached stream must be returned.
     *
     * @return void
     */
    public function testDetach()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);

        $testStream   = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testResource = $testStream->detach();

        $this->assertTrue(is_resource($testResource));
        $this->assertEquals('stream', get_resource_type($testResource));
    }

    /**
     * Test closing stream.
     *
     * @return void
     */
    public function testClose()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream   = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testResource = $testStream->close();
    }

    /**
     * Test get size of stream.
     *
     * @return void
     */
    public function testGetSize()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));

        $this->assertEquals(15, $testStream->getSize());
    }

    /**
     * Test seek and tell function.
     *
     * @return void
     */
    public function testSeekAndTell()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->seek(2);

        $this->assertEquals(2, $testStream->tell());
    }

    /**
     * Test seeking on the detached stream - an exception must be raised.
     *
     * @expectedException \RuntimeException
     * 
     * @return void
     */
    public function testSeekOnDetached()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->detach();
        $testStream->seek(2);
    }

    /**
     * Test tell on the detached stream - an exception must be raised.
     *
     * @expectedException \RuntimeException
     * 
     * @return void
     */
    public function testTellOnDetached()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->detach();
        $testStream->tell();
    }

    /**
     * Test if the stream is on end - in this test it is not.
     *
     * @return void
     */
    public function testEof()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $this->assertEquals(false, $testStream->eof());
    }

    /**
     * Test if the stream is on end - in this test is it.
     *
     * @return void
     */
    public function testEofOnEndOfFile()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->seek(15);
        $this->assertEquals(true, $testStream->eof());
    }

    /**
     * Test write into the stream.
     *
     * @return void
     */
    public function testWrite()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream   = new Stream(fopen($this->root->url().'/test/test.txt', 'r+'));
        $testStream->write('test');
        $this->assertEquals(4, $testStream->write('test'));
    }

    /**
     * Test write into the read only stream - an exception must be raised.
     *
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testWriteException()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->write('test');
    }

    /**
     * Test reading from the stream.
     *
     * @return void
     */
    public function testRead()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $this->assertEquals('con', $testStream->read(3));
    }

    /**
     * Test reading from the detached stream - an exception must be raised.
     *
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testReadExpection()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->detach();
        $testStream->read(3);
    }

    /**
     * Test get contents from the stream.
     *
     * @return void
     */
    public function testGetContents()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));

        $this->assertEquals('content of file', $testStream->getContents());
    }

    /**
     * Test get contents from the detached stream - an exception must be raised.
     *
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testGetContentsExpection()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->detach();
        $testStream->getContents();
    }

    /**
     * Test get metadata from the stream.
     *
     * @return void
     */
    public function testGetMetadata()
    {
        $fileContent = 'content of file';
        $expected    = [
            'timed_out' => false,
            'blocked' => true,
            'eof' => false,
            'mode' => 'r',
            'unread_bytes' => 0,
            'seekable' => true,
            'uri' => 'vfs://root/test/test.txt',
        ];

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));

        $streamMetadata = $testStream->getMetadata();
        unset($streamMetadata['wrapper_data']);
        unset($streamMetadata['wrapper_type']);
        unset($streamMetadata['stream_type']);
        $this->assertEquals($expected, $streamMetadata);
    }

    /**
     * Test get metadata mode from the stream.
     *
     * @return void
     */
    public function testGetMetadataMode()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));

        $this->assertEquals('r', $testStream->getMetadata('mode'));
    }

    /**
     * Test stream typecasting to the string.
     *
     * @return void
     */
    public function testStringTypeCasting()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));

        $this->assertEquals($fileContent, (string)$testStream);
    }

    /**
     * Test typecasting of detached stream to the string - an empty value must be returned.
     *
     * @return void
     */
    public function testStringTypeCastingNoStream()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->detach();
        $this->assertEquals('', (string)$testStream);
    }

    /**
     * Test typecasting of unreadable stream to string, an underline implemenation will 
     * raise an exception. But this method must not allow propagation of this exception
     * as PHP's string casting operations should not raise an exceptions. Instead an empty
     * value will be returned.
     *
     * @return void
     */
    public function testStringCastingNoSeekable()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'c'));

        $this->assertEquals('', (string)$testStream);
    }
}
