<?php
namespace Test\Factories;

// \HttpMessage
use Kambo\HttpMessage\Enviroment\Enviroment;
use Kambo\HttpMessage\ServerRequest;

// \HttpMessage\Factories
use Kambo\HttpMessage\Factories\Enviroment\ServerRequestFactory;

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
     * Test create server request from enviroment.
     * 
     * @return void
     */
    public function testFromEnviroment()
    {
        $enviromentMock = $this->getMockBuilder(Enviroment::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $enviromentMock->method('getRequestScheme')->will($this->returnValue('http'));
        $enviromentMock->method('getHost')->will($this->returnValue('test.com'));
        $enviromentMock->method('getPort')->will($this->returnValue('1111'));
        $enviromentMock->method('getRequestUri')->will($this->returnValue('/path/123?q=abc'));
        $enviromentMock->method('getQueryString')->will($this->returnValue('q=abc'));
        $enviromentMock->method('getAuthUser')->will($this->returnValue('user'));
        $enviromentMock->method('getAuthPassword')->will($this->returnValue('password'));
        $enviromentMock->method('getRequestMethod')->will($this->returnValue('GET'));
        $enviromentMock->method('getServer')->will($this->returnValue([]));
        $enviromentMock->method('getCookies')->will($this->returnValue([]));

        $serverRequest = (new ServerRequestFactory())->create($enviromentMock);
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
    }
}
