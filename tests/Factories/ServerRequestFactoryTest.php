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
    public function testFromEnvironment()
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
        $environmentMock->method('getRequestMethod')->will($this->returnValue('GET'));
        $environmentMock->method('getServer')->will($this->returnValue([]));
        $environmentMock->method('getCookies')->will($this->returnValue([]));

        $serverRequest = (new ServerRequestFactory())->create($environmentMock);
        $this->assertInstanceOf(ServerRequest::class, $serverRequest);
    }
}
