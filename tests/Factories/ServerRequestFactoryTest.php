<?php
namespace Test\Factories;

// \Http\Message
use Kambo\Http\Message\Environment\Environment;
use Kambo\Http\Message\ServerRequest;

// \Http\Message\Factories
use Kambo\Http\Message\Factories\Environment\ServerRequestFactory;

/**
 * Unit test for the ServerRequestFactory object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ServerRequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test create server request from environment.
     * 
     * @return void
     */
    public function testCreate()
    {
        $serverRequest = (new ServerRequestFactory())->create($this->getEnviromentMock());
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
    }

    /**
     * Test create server request from environment with content type multipart/form-data.
     *
     * @return void
     */
    public function testCreateMultipartFormData()
    {
        $serverRequest = (new ServerRequestFactory())->create(
            $this->getEnviromentMock(['CONTENT_TYPE'=>'multipart/form-data'])
        );
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
        $this->assertEquals(['foo'=>'bar'], $serverRequest->getParsedBody());
    }

    /**
     * Test create server request from environment with content type application/x-www-form-urlencoded.
     *
     * @return void
     */
    public function testCreateFormUrlencoded()
    {
        $serverRequest = (new ServerRequestFactory())->create(
            $this->getEnviromentMock(['CONTENT_TYPE'=>'application/x-www-form-urlencoded'])
        );
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
        $this->assertEquals(['foo'=>'bar'], $serverRequest->getParsedBody());
    }

    /**
     * Get mocked enviroment object for the test
     *
     * @param array $server Values that will be returned in getServer method of enviroment object
     *
     * @return Environment Environment instance for the testing.
     */
    private function getEnviromentMock($server = [])
    {
        $environmentMock = $this->getMockBuilder(Environment::class)
                                ->disableOriginalConstructor()
                                ->getMock();

        $environmentMock->method('getRequestScheme')->will($this->returnValue('http'));
        $environmentMock->method('getHost')->will($this->returnValue('test.com'));
        $environmentMock->method('getPort')->will($this->returnValue('1111'));
        $environmentMock->method('getRequestUri')->will($this->returnValue('/path/123?q=abc'));
        $environmentMock->method('getQueryString')->will($this->returnValue('q=abc'));
        $environmentMock->method('getAuthUser')->will($this->returnValue('user'));
        $environmentMock->method('getAuthPassword')->will($this->returnValue('password'));
        $environmentMock->method('getRequestMethod')->will($this->returnValue('POST'));
        $environmentMock->method('getServer')->will($this->returnValue($server));
        $environmentMock->method('getCookies')->will($this->returnValue([]));
        $environmentMock->method('getPost')->will($this->returnValue(['foo'=>'bar']));

        return $environmentMock;
    }
}
