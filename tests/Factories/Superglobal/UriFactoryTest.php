<?php
namespace Test\Factories\Superglobal;

// \HttpMessage
use Kambo\HttpMessage\Enviroment\Enviroment;
use Kambo\HttpMessage\Factories\Enviroment\Superglobal\UriFactory;
use Kambo\HttpMessage\Uri;

/**
 * Unit test for the UriFactory object.
 *
 * @package Test\Factories\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test creating URI object from enviroment.
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

        $uri = (new UriFactory())->create($enviromentMock);

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEquals(null, $uri->getFragment());
        $this->assertEquals('test.com', $uri->getHost());
        $this->assertEquals('/path/123', $uri->getPath());
        $this->assertEquals(1111, $uri->getPort());
        $this->assertEquals('q=abc', $uri->getQuery());
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('user:password', $uri->getUserInfo());
    }
}
