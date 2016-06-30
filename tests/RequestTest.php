<?php
namespace Test;

// \HttpMessage
use Kambo\HttpMessage\Request;
use Kambo\HttpMessage\Uri;
use Kambo\HttpMessage\Factories\String\UriFactory;

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
     * Test creating object with invalid parameters
     * 
     * @return void
     */
    public function testObjectCreationWithBodyString()
    {
        $serverRequest = new Request('GET', 'www.test.com', null, 'body');
        $this->assertEquals('body', (string)$serverRequest->getBody());
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
        $serverRequest = new Request('GET', 'www.test.com', null, new \stdClass);
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
        $this->assertEquals($url, (string)$serverRequest->getUri());
    }

    /**
     * Test changing uri of the request, operation must be immutable - a new instance of object
     * must be created and previous instance must retain its value.
     * 
     * @return void
     */
    public function testWithUri()
    {
        $url    = 'http://user:password@test.com:1111/path/123?q=abc#test';
        $newUrl = 'http://foo.com/bar?parameter=value';

        $serverRequest = new Request('GET', $url);
        $newRequest    = $serverRequest->withUri(UriFactory::create($newUrl));

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertEquals($url, (string)$serverRequest->getUri());

        $this->assertInstanceOf(Uri::class, $newRequest->getUri());
        $this->assertEquals($newUrl, (string)$newRequest->getUri());
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
        $url    = 'http://user:password@test.com:1111/path/123?q=abc#test';
        $newUrl = 'http://foo.com/bar?parameter=value';

        $serverRequest = new Request('GET', $url);
        $newRequest    = $serverRequest->withUri(UriFactory::create($newUrl), true);

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertEquals($url, (string)$serverRequest->getUri());

        $this->assertInstanceOf(Uri::class, $newRequest->getUri());
        $this->assertEquals($newUrl, (string)$newRequest->getUri());
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
        $url    = 'http://user:password@test.com:1111/path/123?q=abc#test';
        $newUrl = 'http://foo.com/bar?parameter=value';

        $serverRequest = new Request('GET', $url);
        $newRequest    = $serverRequest->withoutHeader('Host');
        $newRequest    = $newRequest->withUri(UriFactory::create($newUrl), true);

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertEquals($url, (string)$serverRequest->getUri());

        $this->assertInstanceOf(Uri::class, $newRequest->getUri());
        $this->assertEquals($newUrl, (string)$newRequest->getUri());

        $this->assertEquals(['foo.com'], $newRequest->getHeader('Host'));
    }
}
