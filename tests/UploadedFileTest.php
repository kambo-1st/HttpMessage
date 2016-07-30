<?php
namespace Test;

// \Spl
use ReflectionClass;

// \Http\Message
use Kambo\Http\Message\Stream;
use Kambo\Http\Message\UploadedFile;
use Kambo\Http\Message\Utils\UploadFile as UploadFileUtils;

// \vfs
use org\bovigo\vfs\vfsStream;

/**
 * Unit test for the UploadedFile object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UploadedFileTest extends \PHPUnit_Framework_TestCase
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
     * Test creating new instance of UploadFile
     *
     * @return void
     */
    public function testUploadFileCreate()
    {
        $testedClass = new UploadedFile('tmp/test.txt', 'test.txt', 'text/html', 1024, 0);

        $this->assertEquals(1024, $testedClass->getSize());
        $this->assertEquals(0, $testedClass->getError());
        $this->assertEquals('test.txt', $testedClass->getClientFilename());
        $this->assertEquals('text/html', $testedClass->getClientMediaType());
    }

    /**
     * Test retrieving a stream representing the uploaded file.
     *
     * @return void
     */
    public function testGetStream()
    {
        $fileContent = 'content of file';
        
        $temp = vfsStream::newDirectory('temp')->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testedClass = new UploadedFile(
            $this->root->url().'/temp/test.txt',
            'test.txt',
            'text/html',
            1024,
            0
        );
        $stream = $testedClass->getStream();

        $this->assertInstanceOf(Stream::class, $stream);

        $reflectionStream         = new ReflectionClass(Stream::class);
        $reflectionStreamProperty = $reflectionStream->getProperty('stream');
        $reflectionStreamProperty->setAccessible(true);
        $streamUnderlineStream = $reflectionStreamProperty->getValue($stream);

        $this->assertEquals($fileContent, stream_get_contents($streamUnderlineStream));
    }

    /**
     * Test moving the uploaded file to a new location.
     *
     * @return void
     */
    public function testMoveTo()
    {
        $fileContent = 'The new contents of the file';
        
        $tempDir = vfsStream::newDirectory('temp')->at($this->root);
        vfsStream::newDirectory('target')->at($this->root);
        vfsStream::newFile('test.txt')->at($tempDir)->setContent($fileContent);

        $uploadFileMock = $this->getMockBuilder(UploadFileUtils::class)->getMock();
        $uploadFileMock->method('is')->willReturn(true);
        $uploadFileMock->method('move')->will(
            $this->returnCallback(
                function ($filename, $destination) {
                    return rename($filename, $destination);
                }
            )
        );

        $testedClass = new UploadedFile(
            $this->root->url().'/temp/test.txt',
            'test.txt',
            'text/html',
            1024,
            0,
            $uploadFileMock
        );

        $testedClass->moveTo($this->root->url().'/target/moved.txt');
        $this->assertTrue($this->root->hasChild('target/moved.txt'));
    }

    /**
     * Test moving the uploaded file to non writable target.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testMoveToTargetNotWritable()
    {
        $fileContent = 'The new contents of the file';
        
        $temp = vfsStream::newDirectory('temp')->at($this->root);
        // Set non writable file mode to target file.
        $temp = vfsStream::newDirectory('target', 0004)->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testedClass = new UploadedFile($this->root->url().'/temp/test.txt', 'test.txt', 'text/html', 1024, 0);

        $testedClass->moveTo($this->root->url().'/target/moved.txt');
    }

    /**
     * Test moving a non valid uploaded file.
     *
     * @expectedException \RuntimeException
     *
     * @return void     
     */
    public function testMoveToNotValidFile()
    {
        $fileContent = 'The new contents of the file';
        
        $tempDir = vfsStream::newDirectory('temp')->at($this->root);
        vfsStream::newDirectory('target')->at($this->root);
        vfsStream::newFile('test.txt')->at($tempDir)->setContent($fileContent);
        $testedClass = new UploadedFile($this->root->url().'/temp/test.txt', 'test.txt', 'text/html', 1024, 0);

        $testedClass->moveTo($this->root->url().'/target/moved.txt');
    }

    /**
     * Test moving an already moved file.
     *
     * @expectedException \RuntimeException
     *
     * @return void     
     */
    public function testMoveToFileAlreadyMoved()
    {
        $fileContent = 'The new contents of the file';
        
        $tempDir = vfsStream::newDirectory('temp')->at($this->root);
        vfsStream::newDirectory('target')->at($this->root);
        vfsStream::newFile('test.txt')->at($tempDir)->setContent($fileContent);

        $uploadFileMock = $this->getMockBuilder(UploadFileUtils::class)->getMock();
        $uploadFileMock->method('is')->willReturn(true);
        $uploadFileMock->method('move')->will(
            $this->returnCallback(
                function ($filename, $destination) {
                    return rename($filename, $destination);
                }
            )
        );

        $testedClass = new UploadedFile(
            $this->root->url().'/temp/test.txt',
            'test.txt',
            'text/html',
            1024,
            0,
            $uploadFileMock
        );

        $testedClass->moveTo($this->root->url().'/target/moved.txt');
        $testedClass->moveTo($this->root->url().'/target/moved2.txt');
    }

    /**
     * Test moving the uploaded file with move operation fail. 
     *
     * @expectedException \RuntimeException
     *
     * @return void     
     */
    public function testMoveToMoveFileFail()
    {
        $fileContent = 'The new contents of the file';
        
        $tempDir = vfsStream::newDirectory('temp')->at($this->root);
        vfsStream::newDirectory('target')->at($this->root);
        vfsStream::newFile('test.txt')->at($tempDir)->setContent($fileContent);

        $uploadFileMock = $this->getMockBuilder(UploadFileUtils::class)->getMock();
        $uploadFileMock->method('is')->willReturn(true);
        $uploadFileMock->method('move')->willReturn(false);

        $testedClass = new UploadedFile(
            $this->root->url().'/temp/test.txt',
            'test.txt',
            'text/html',
            1024,
            0,
            $uploadFileMock
        );

        $testedClass->moveTo($this->root->url().'/target/moved.txt');
        $testedClass->moveTo($this->root->url().'/target/moved2.txt');
    }

    /**
     * Test getting stream of already moved file - an exception should be raised.
     *
     * @expectedException \RuntimeException
     *
     * @return void     
     */
    public function testGetStreamException()
    {
        $fileContent = 'The new contents of the file';

        $tempDir = vfsStream::newDirectory('temp')->at($this->root);
        vfsStream::newDirectory('target')->at($this->root);
        vfsStream::newFile('test.txt')->at($tempDir)->setContent($fileContent);

        $uploadFileMock = $this->getMockBuilder(UploadFileUtils::class)->getMock();
        $uploadFileMock->method('is')->willReturn(true);
        $uploadFileMock->method('move')->will(
            $this->returnCallback(
                function ($filename, $destination) {
                    return rename($filename, $destination);
                }
            )
        );

        $testedClass = new UploadedFile(
            $this->root->url().'/temp/test.txt',
            'test.txt',
            'text/html',
            1024,
            0,
            $uploadFileMock
        );

        $testedClass->moveTo($this->root->url().'/target/moved.txt');
        // File has been moved thus exception should be raised.
        $testedClass->getStream();
    }
}
