<?php
namespace Test\Factories\Superglobal;

// \HttpMessage
use Kambo\HttpMessage\Enviroment\Enviroment;
use Kambo\HttpMessage\Factories\Enviroment\Superglobal\FilesFactory;
use Kambo\HttpMessage\UploadedFile;

/**
 * Unit test for the FilesFactory object.
 *
 * @package Test\Factories\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class FilesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creating headers from enviroment object with one file in one field.
     *
     * @return void
     */
    public function testCreateFromEnviroment()
    {
        $uploadSuperglobal = [
            "upload" => [
                "name" => "file0.txt",
                "type" => "text/plain",
                "tmp_name" => "/tmp/phpYzdqkD",
                "error" => 0,
                "size" => 54654654,
            ]
        ];

        $enviroment    = $this->getEnviromentMock($uploadSuperglobal);
        $uploadedFiles = (new FilesFactory())->create($enviroment);

        $this->assertInternalType('array', $uploadedFiles);
        $this->assertArrayHasKey('upload', $uploadedFiles);
        $this->assertCount(1, $uploadedFiles['upload']);

        list($uploadedFile) = $uploadedFiles['upload'];

        $this->assertInstanceOf(UploadedFile::class, $uploadedFile);
        $this->assertEquals(54654654, $uploadedFile->getSize());
        $this->assertEquals(0, $uploadedFile->getError());
        $this->assertEquals('file0.txt', $uploadedFile->getClientFilename());
        $this->assertEquals('text/plain', $uploadedFile->getClientMediaType());
    }

    /**
     * Test creating headers from enviroment object with multiple files in multiple fields.
     *
     * @return void
     */
    public function testCreateFromEnviromentMultipleFieldsAndFiles()
    {
        $uploadSuperglobal = [
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

        $enviroment    = $this->getEnviromentMock($uploadSuperglobal);
        $uploadedFiles = (new FilesFactory())->create($enviroment);

        $this->assertInternalType('array', $uploadedFiles);
        $this->assertArrayHasKey('upload', $uploadedFiles);
        $this->assertArrayHasKey('second_upload', $uploadedFiles);
        $this->assertCount(2, $uploadedFiles['upload']);
        $this->assertCount(2, $uploadedFiles['second_upload']);

        list($firstFile) = $uploadedFiles['upload'];
        $this->assertInstanceOf(UploadedFile::class, $firstFile);
        $this->assertEquals(54654654, $firstFile->getSize());
        $this->assertEquals(0, $firstFile->getError());
        $this->assertEquals('file0.txt', $firstFile->getClientFilename());
        $this->assertEquals('text/plain', $firstFile->getClientMediaType());

        list(,$secondFile) = $uploadedFiles['second_upload'];
        $this->assertInstanceOf(UploadedFile::class, $secondFile);
        $this->assertEquals(4565467, $secondFile->getSize());
        $this->assertEquals(0, $secondFile->getError());
        $this->assertEquals('file3.txt', $secondFile->getClientFilename());
        $this->assertEquals('text/html', $secondFile->getClientMediaType());
    }

    // ------------ PRIVATE METHODS

    /**
     * Get instance of mocked Enviroment object for testing purpose.
     *
     * @param array $filesSuperglobal array in same format as files superglobal
     *                                variable ($_FILES).
     *
     * @return Enviroment
     */
    private function getEnviromentMock(array $filesSuperglobal = [])
    {
        $enviromentMock = $this->getMockBuilder(Enviroment::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $enviromentMock->method('getFiles')->will(
            $this->returnValue($filesSuperglobal)
        );

        return $enviromentMock;
    }
}
