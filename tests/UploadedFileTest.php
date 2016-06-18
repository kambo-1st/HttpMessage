<?php
namespace Test;

// \HttpMessage
use Kambo\HttpMessage\Enviroment\Enviroment;
use Kambo\HttpMessage\Factories\Enviroment\Superglobal\FilesFactory;
use Kambo\HttpMessage\UploadedFile;
use Kambo\HttpMessage\Utils\UploadFile as UploadFileUtils;

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
    private $root;

    /**
     *
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
    public function testUploadFileCreate()
    {
        $testedClass = new UploadedFile('tmp/test.txt', 'test.txt', 'text/html', 1024, 0);

        $this->assertEquals(1024, $testedClass->getSize());
        $this->assertEquals(0, $testedClass->getError());
        $this->assertEquals('test.txt', $testedClass->getClientFilename());
        $this->assertEquals('text/html', $testedClass->getClientMediaType());
    }

    /**
     *
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

        $this->assertEquals($fileContent, $stream->getContents());
    }

    /**
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMoveTo2()
    {
        $fileContent = 'The new contents of the file';
        
        $temp = vfsStream::newDirectory('temp')->at($this->root);
        $temp = vfsStream::newDirectory('target', 0001)->at($this->root);
        vfsStream::newFile('test.txt')->at($temp)->setContent($fileContent);
        $testedClass = new UploadedFile($this->root->url().'/temp/test.txt', 'test.txt', 'text/html', 1024, 0);

        $testedClass->moveTo($this->root->url().'/target/moved.txt');
    }

    /**
     *
     * @expectedException \RuntimeException
     */
    public function testMoveTo()
    {
        $fileContent = 'The new contents of the file';
        
        $tempDir = vfsStream::newDirectory('temp')->at($this->root);
        vfsStream::newDirectory('target')->at($this->root);
        vfsStream::newFile('test.txt')->at($tempDir)->setContent($fileContent);
        $testedClass = new UploadedFile($this->root->url().'/temp/test.txt', 'test.txt', 'text/html', 1024, 0);

        $testedClass->moveTo($this->root->url().'/target/moved.txt');
    }

    /**
     *
     *
     * @return void
     */
    public function testMoveTo3()
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
     *
     * @expectedException \RuntimeException
     *
     */
    public function testMoveTo4()
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
     *
     * @expectedException \RuntimeException
     *
     */
    public function testMoveTo5()
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
     *
     * @expectedException \RuntimeException
     *
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
        // file has been moved should raise exception
        $testedClass->getStream();
    }

    /**
     *
     *
     * @return void
     */
    public function testGetAll()
    {
        $uploadedFiles = $this->getUploadedFilesForTest();

        $this->assertArrayHasKey('upload', $uploadedFiles);
        $this->assertArrayHasKey('second_upload', $uploadedFiles);
        $this->assertCount(2, $uploadedFiles['upload']);
        $this->assertCount(2, $uploadedFiles['second_upload']);

        list($firstFile) = $uploadedFiles['upload'];
        $this->assertEquals(54654654, $firstFile->getSize());
        $this->assertEquals(0, $firstFile->getError());
        $this->assertEquals('file0.txt', $firstFile->getClientFilename());
        $this->assertEquals('text/plain', $firstFile->getClientMediaType());

        list(,$secondFile) = $uploadedFiles['second_upload'];
        $this->assertEquals(4565467, $secondFile->getSize());
        $this->assertEquals(0, $secondFile->getError());
        $this->assertEquals('file3.txt', $secondFile->getClientFilename());
        $this->assertEquals('text/html', $secondFile->getClientMediaType());
    }

    /**
     *
     *
     * @return void
     */
    public function testGetAll2()
    {
        $uploadedFiles = $this->getUploadedFilesForTest2();

        $this->assertArrayHasKey('upload', $uploadedFiles);
        $this->assertCount(1, $uploadedFiles['upload']);

        list($firstFile) = $uploadedFiles['upload'];
        $this->assertEquals(54654654, $firstFile->getSize());
        $this->assertEquals(0, $firstFile->getError());
        $this->assertEquals('file0.txt', $firstFile->getClientFilename());
        $this->assertEquals('text/plain', $firstFile->getClientMediaType());
    }

    // ------------ PRIVATE METHODS

    /**
     *
     *
     * @return UploadedFile
     */
    private function getUploadedFilesForTest()
    {
        $enviroment = new Enviroment([], fopen('php://memory','r+'), [], $this->getTestData());
        return FilesFactory::fromEnviroment($enviroment);
    }

    /**
     *
     *
     * @return UploadedFile
     */
    private function getUploadedFilesForTest2()
    {
        $enviroment = new Enviroment([], fopen('php://memory','r+'), [], $this->getTestData2());
        return FilesFactory::fromEnviroment($enviroment);
    }

    /**
     *
     *
     * @return array
     */
    private function getTestData2()
    {
        return [
            "upload" => [
                "name" => "file0.txt",
                "type" => "text/plain",
                "tmp_name" => "/tmp/phpYzdqkD",
                "error" => 0,
                "size" => 54654654,
            ]
        ];
    }

    /**
     *
     *
     * @return array
     */
    private function getTestData()
    {
        return [
            "upload" => [
                "name" => [
                    "file0.txt",
                    "file1.txt"
                ],
                "type" => [
                    "text/plain",
                    "text/html"
                ],
                "tmp_name" => [
                    "/tmp/phpYzdqkD",
                    "/tmp/phpeEwEWG"
                ],
                "error" => [
                    0,
                    0
                ],
                "size" => [
                    54654654,
                    4567
                ],
            ],
            "second_upload" => [
                "name" => [
                    "file2.txt",
                    "file3.txt"
                ],
                "type" => [
                    "text/plain",
                    "text/html"
                ],
                "tmp_name" => [
                    "/tmp/phpYzdqkD",
                    "/tmp/phpeEwEWG"
                ],
                "error" => [
                    0,
                    0
                ],
                "size" => [
                    4556,
                    4565467
                ],
            ],
        ];
    }
}
