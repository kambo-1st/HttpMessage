<?php
namespace Test;

// \Spl
use ReflectionClass;
use RuntimeException;

// \HttpMessage
use Kambo\HttpMessage\Stream;

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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
     *
     * @expectedException \RuntimeException
     * 
     * @return void
     */
    public function testSeekException()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->detach();
        $testStream->seek(2);
    }

    /**
     *
     *
     * @expectedException \RuntimeException
     * 
     * @return void
     */
    public function testTellException()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->detach();
        $testStream->tell();
    }

    /**
     *
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
     *
     *
     * @return void
     */
    public function testEofTrue()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->seek(15);
        $this->assertEquals(true, $testStream->eof());
    }

    /**
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
            'wrapper_type' => 'user-space',
            'stream_type' => 'user-space',
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
        $this->assertEquals($expected, $streamMetadata);
    }

    /**
     *
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
     *
     * 
     * @return void
     */
    public function testStringCasting()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));

        $this->assertEquals($fileContent, (string)$testStream);
    }

    /**
     *
     * 
     * @return void
     */
    public function testStringCastingNoStream()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testStream = new Stream(fopen($this->root->url().'/test/test.txt', 'r'));
        $testStream->detach();
        $this->assertEquals('', (string)$testStream);
    }

    /**
     *
     * 
     * @return void
     */
    public function testStringCastingNoSeekable()
    {
        $fileContent = 'content of file';

        $temp = vfsStream::newDirectory('test')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);

        $testStream = new StubStream(fopen($this->root->url().'/test/test.txt', 'r'));
        $this->assertEquals('', (string)$testStream);
    }
}

// @codingStandardsIgnoreStart
class StubStream extends Stream
{
    public function rewind()
    {
        throw new RuntimeException('Could not read from stream');
    }
}
// @codingStandardsIgnoreEnd
