<?php
namespace Test\Factories\Superglobal;

// \Http\Message
use Kambo\Http\Message\Environment\Environment;
use Kambo\Http\Message\Factories\Environment\Superglobal\UriFactory;
use Kambo\Http\Message\Uri;

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
     * Test creating URI object from environment.
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

        $uri = (new UriFactory())->create($environmentMock);

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEquals(null, $uri->getFragment());
        $this->assertEquals('test.com', $uri->getHost());
        $this->assertEquals('/path/123', $uri->getPath());
        $this->assertEquals(1111, $uri->getPort());
        $this->assertEquals('q=abc', $uri->getQuery());
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('user:password', $uri->getUserInfo());
    }

    /**
     * Test creating URI object from environment - try to construct URI from
     * invalid request URI ane exception must be raised.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testFromEnvironmentFail()
    {

        $environmentMock = $this->getMockBuilder(Environment::class)
                                ->disableOriginalConstructor()
                                ->getMock();

        $environmentMock->method('getRequestScheme')->will($this->returnValue('http'));
        $environmentMock->method('getHost')->will($this->returnValue('test.com'));
        $environmentMock->method('getPort')->will($this->returnValue('1111'));
        $environmentMock->method('getRequestUri')->will($this->returnValue('@'));
        $environmentMock->method('getQueryString')->will($this->returnValue('q=abc'));
        $environmentMock->method('getAuthUser')->will($this->returnValue('user'));
        $environmentMock->method('getAuthPassword')->will($this->returnValue('password'));

        $uri = (new UriFactory())->create($environmentMock);
    }
}
