<?php
namespace Test;

// \Http\Message
use Kambo\Http\Message\Request;
use Kambo\Http\Message\Uri;

/**
 * Unit test for the Request object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creating object with uri as string
     * 
     * @return void
     */
    public function testObjectCreationWithBodyString()
    {
        $serverRequest = new Request('GET', 'www.test.com', [], 'body');
        $this->assertEquals('body', (string)$serverRequest->getBody());
    }

    /**
     * Test creating object with uri as instance of Uri class.
     * 
     * @return void
     */
    public function testObjectCreationWithUriObject()
    {
        $url = $this->getUriMockForTests();
        $serverRequest = new Request('GET', $url);
        $this->assertEquals($url, $serverRequest->getUri());
    }

    /**
     * Test creating object with invalid parameters
     *
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testInvalidUriObjectCreation()
    {
        $serverRequest = new Request('GET', []);
    }

    /**
     * Test creating object with invalid parameters
     *
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testInvalidBodyObjectCreation()
    {
        $serverRequest = new Request('GET', 'www.test.com', [], new \stdClass);
    }

    /**
     * Test get request target
     * 
     * @return void
     */
    public function testGetRequestTarget()
    {
        $serverRequest = new Request('GET', 'http://user:password@test.com:1111/path/123?q=abc#test');
        $this->assertEquals('/path/123?q=abc', $serverRequest->getRequestTarget());
    }

    /**
     * Test adding new request target, operation must be immutable - a new instance of object
     * must be created and previous instance must retain its value.
     * 
     * @return void
     */
    public function testWithRequestTarget()
    {
        $serverRequest = new Request('GET', 'http://user:password@test.com:1111/path/123?q=abc#test');
        $newRequest    = $serverRequest->withRequestTarget('/foo/?bar=test');

        $this->assertEquals('/path/123?q=abc', $serverRequest->getRequestTarget());
        $this->assertEquals('/foo/?bar=test', $newRequest->getRequestTarget());
    }

    /**
     * Test get method of the request
     * 
     * @return void
     */
    public function testGetMethod()
    {
        $serverRequest = new Request('GET', 'http://user:password@test.com:1111/path/123?q=abc#test');
        $this->assertEquals('GET', $serverRequest->getMethod());
    }

    /**
     * Test changing method of the request, operation must be immutable - a new instance of object
     * must be created and previous instance must retain its value.
     * 
     * @return void
     */
    public function testWithMethod()
    {
        $serverRequest = new Request('GET', 'http://user:password@test.com:1111/path/123?q=abc#test');
        $newRequest    = $serverRequest->withMethod('POST');

        $this->assertEquals('GET', $serverRequest->getMethod());
        $this->assertEquals('POST', $newRequest->getMethod());
    }

    /**
     * Test changing method of the request, with invalid request method. Exception
     * must be thrown.
     *
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testWithMethodInvalid()
    {
        $serverRequest = new Request('GET', 'http://user:password@test.com:1111/path/123?q=abc#test');
        $newRequest    = $serverRequest->withMethod('TEST');
    }

    /**
     * Test get uri from the request
     * 
     * @return void
     */
    public function testGetUri()
    {
        $url           = 'http://user:password@test.com:1111/path/123?q=abc#test';
        $serverRequest = new Request('GET', $url);

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
    }

    /**
     * Test changing uri of the request, operation must be immutable - a new instance of object
     * must be created and previous instance must retain its value.
     * 
     * @return void
     */
    public function testWithUri()
    {
        $url = 'http://user:password@test.com:1111/path/123?q=abc#test';

        $serverRequest = new Request('GET', $url);
        $newRequest    = $serverRequest->withUri($this->getUriMockForTests());

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertInstanceOf(Uri::class, $newRequest->getUri());

        $this->assertNotSame($serverRequest, $newRequest);
        $this->assertNotSame($serverRequest->getUri(), $newRequest->getUri());
    }

    /**
     * Test changing uri of the request with preserving the host, operation must be 
     * immutable - a new instance of object must be created and previous instance must
     * retain its value.
     * 
     * @return void
     */
    public function testWithUriPreserveHost()
    {
        $url = 'http://user:password@test.com:1111/path/123?q=abc#test';

        $serverRequest = new Request('GET', $url);
        $newRequest    = $serverRequest->withUri($this->getUriMockForTests(), true);

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertInstanceOf(Uri::class, $newRequest->getUri());

        $this->assertNotSame($serverRequest, $newRequest);
        $this->assertNotSame($serverRequest->getUri(), $newRequest->getUri());
    }

    /**
     * Test changing uri of the request with preserving the host, but without host header.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithUriPreserveHostNoHostHeader()
    {
        $url = 'http://user:password@test.com:1111/path/123?q=abc#test';

        $serverRequest = new Request('GET', $url);
        $newRequest    = $serverRequest->withoutHeader('Host');
        $newRequest    = $newRequest->withUri($this->getUriMockForTests(), true);

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertInstanceOf(Uri::class, $newRequest->getUri());

        $this->assertNotSame($serverRequest, $newRequest);
        $this->assertNotSame($serverRequest->getUri(), $newRequest->getUri());

        $this->assertEquals(['foo.com'], $newRequest->getHeader('Host'));
    }

    /**
     * Get mocked instance of URI object for the testing.
     * 
     * @return Uri Uri instance for the testing.
     */
    private function getUriMockForTests()
    {
        $uriMock = $this->getMockBuilder(Uri::class)->disableOriginalConstructor()->getMock();
        $uriMock->method('getHost')->willReturn('foo.com');

        return $uriMock;
    }
}
