<?php
namespace Test;

// \Http\Message
use Kambo\Http\Message\Headers;
use Kambo\Http\Message\ServerRequest;
use Kambo\Http\Message\Stream;
use Kambo\Http\Message\UploadedFile;
use Kambo\Http\Message\Uri;

// \Http\Message\Factories
use Kambo\Http\Message\Factories\String\UriFactory;

/**
 * Unit test for the ServerRequest object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ServerRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get request target
     * 
     * @return void
     */
    public function testGetRequestTarget()
    {
        $serverRequest = $this->getServerRequestForTest();

        $this->assertEquals('/path/123?q=abc', $serverRequest->getRequestTarget());
    }

    /**
     * Test get server params - server variable should be same as the one
     * which has been used for the instance initialization.
     * 
     * @return void
     */
    public function testGetServerParams()
    {
        $serverRequest = $this->getServerRequestForTest();
        $expected = [
            'HTTP_HOST' => 'test.com',
        ];

        $this->assertEquals($expected, $serverRequest->getServerParams());
    }

    /**
     * Test get cookie params
     * 
     * @return void
     */
    public function testGetCookieParams()
    {
        $cookie        = ['foo' => 'bar', 'name' => 'value'];
        $serverRequest = $this->getServerRequestForTest(null, $cookie);
        $expected      = [
            "foo" => "bar",
            "name" => "value"
        ];

        $this->assertEquals($expected, $serverRequest->getCookieParams());
    }

    /**
     * Test changing cookie params.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithCookieParams()
    {
        $cookie        = ['foo' => 'bar', 'name' => 'value'];
        $serverRequest = $this->getServerRequestForTest(null, $cookie);
        $expected      = [
            "foo" => "bar",
            "name" => "value"
        ];

        $cookiesNewRequest = [
            "foo" => "bar",
            "name" => "value"
        ];

        $newServerRequest = $serverRequest->withCookieParams($cookiesNewRequest);

        $this->assertEquals($cookiesNewRequest, $newServerRequest->getCookieParams());
        $this->assertEquals($expected, $serverRequest->getCookieParams());
    }

    /**
     * Test changing request target.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithRequestTarget()
    {
        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->withRequestTarget('/foo/?bar=test');

        // check if was not changed
        $this->assertEquals('/path/123?q=abc', $serverRequest->getRequestTarget());
        $this->assertEquals('/foo/?bar=test', $newRequest->getRequestTarget());
    }

    /**
     * Test get request method.
     * 
     * @return void
     */
    public function testGetMethod()
    {
        $serverRequest = $this->getServerRequestForTest();

        $this->assertEquals('GET', $serverRequest->getMethod());
    }

    /**
     * Test changing request method.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithMethod()
    {
        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->withMethod('POST');

        // check if was not changed in original instance of object
        $this->assertEquals('GET', $serverRequest->getMethod());
        $this->assertEquals('POST', $newRequest->getMethod());
    }

    /**
     * Test changing request method to invalid value an exception must be thrown.
     *
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testWithMethodInvalid()
    {
        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->withMethod('TEST');
    }

    /**
     * Test get uri from the request.
     * 
     * @return void
     */
    public function testGetUri()
    {
        $serverRequest = $this->getServerRequestForTest();

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
    }

    /**
     * Test changing uri of request.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithUri()
    {
        $url    = 'http://user:password@www.example.com:1111/path/123?q=abc#fragment';
        $newUrl = 'http://foo.com/bar?parameter=value';

        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->withUri((new UriFactory())->create($newUrl));

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertEquals($url, (string)$serverRequest->getUri());

        $this->assertInstanceOf(Uri::class, $newRequest->getUri());
        $this->assertEquals($newUrl, (string)$newRequest->getUri());
    }

    /**
     * Test get query params from the request.
     * 
     * @return void
     */
    public function testGetQueryParams()
    {
        $serverRequest = $this->getServerRequestForTest();
        $expected      = [
            'q' => 'abc'
        ];

        $this->assertEquals($expected, $serverRequest->getQueryParams());
    }

    /**
     * Test changing query params of request.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithQueryParams()
    {
        $serverRequest = $this->getServerRequestForTest();

        $expected = [
            'q' => 'abc'
        ];
        $newQuery = [
            'foo' => 'bar'
        ];

        $newRequest = $serverRequest->withQueryParams($newQuery);

        $this->assertEquals($expected, $serverRequest->getQueryParams());
        $this->assertEquals($newQuery, $newRequest->getQueryParams());
    }

    /**
     * Test get upload files
     * 
     * @return void
     */
    public function testGetUploadedFiles()
    {
        $serverRequest = $this->getServerRequestForTest();
        $this->assertEquals([], $serverRequest->getUploadedFiles());
    }

    /**
     * Test changing upload files.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithUploadedFiles()
    {
        $newFiles = [
            'upload-field' => [
                new UploadedFile('tmp/test.txt', 'test.txt', 'text/plain', 1024, 0),
                new UploadedFile('tmp/test2.txt', 'test2.txt', 'text/plain', 2048, 0)
            ],
            'second-upload-field' => [
                new UploadedFile('tmp/test3.txt', 'test3.txt', 'text/plain', 4096, 0),
                new UploadedFile('tmp/test4.txt', 'test4.txt', 'text/plain', 8192, 0)
            ]
        ];

        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->WithUploadedFiles($newFiles);

        $this->assertEquals([], $serverRequest->getUploadedFiles());
        $this->assertEquals($newFiles, $newRequest->getUploadedFiles());
    }

    /**
     * Test changing upload files with invalid values.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithUploadedFilesInvalid()
    {
        $newFiles = [
            'foo' => 'bar'
        ];

        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->WithUploadedFiles($newFiles);
    }

    /**
     * Test get empty (null) parsed body of the request.
     * 
     * @return void
     */
    public function testGetParsedBody()
    {
        $serverRequest = $this->getServerRequestForTest();
        $this->assertEquals(null, $serverRequest->getParsedBody());
    }

    /**
     * Test get urlencoded parsed body of the request.
     * 
     * @return void
     */
    public function testGetParsedBodyFormEncode()
    {

        $headersMock = $this->getMockBuilder(Headers::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $headersMock->method('exists')->will($this->returnValue(true));
        $headersMock->method('get')->will($this->returnValue(['application/x-www-form-urlencoded']));

        $serverRequest = $this->getServerRequestForTest(
            'test=test&submit=Test',
            [],
            $headersMock
        );
        $expected = [
            "test" => "test",
            "submit" => "Test"
        ];

        $this->assertEquals($expected, $serverRequest->getParsedBody());
    }

    /**
     * Test with new parsed body.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithParsedBody()
    {
        $headersMock = $this->getMockBuilder(Headers::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $headersMock->method('exists')->will($this->returnValue(true));
        $headersMock->method('get')->will($this->returnValue(['application/x-www-form-urlencoded']));

        $serverRequest = $this->getServerRequestForTest(
            'test=test&submit=Test',
            [],
            $headersMock
        );
        $expected = [
            "test" => "test",
            "submit" => "Test"
        ];
        $newBody = [
            "foo" => "bar",
            "bar" => "foo"
        ];

        $newRequest = $serverRequest->withParsedBody($newBody);

        $this->assertEquals($newBody, $newRequest->getParsedBody());
        $this->assertEquals($expected, $serverRequest->getParsedBody());
    }

    /**
     * Test with invalid, non parsed body
     *
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testWithParsedBodyInvalidArgument()
    {
        $serverRequest = $this->getServerRequestForTest();
        $newRequest = $serverRequest->withParsedBody('value cannot be string');
    }

    /**
     * Test get attributes from request
     * 
     * @return void
     */
    public function testGetAttributes()
    {
        $serverRequest = $this->getServerRequestForTest();
        $this->assertEquals([], $serverRequest->getAttributes());
    }

    /**
     * Test adding new attributes
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     * 
     * @return void
     */
    public function testWithAttribute()
    {
        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->withAttribute('foo', 'bar');

        $this->assertEquals('bar', $newRequest->getAttribute('foo'));
        $this->assertEquals(null, $serverRequest->getAttribute('foo'));
    }

    /**
     * Test get attribute from request
     * 
     * @return void
     */
    public function testGetAttribute()
    {
        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->withAttribute('foo', 'bar');

        $this->assertEquals('bar', $newRequest->getAttribute('foo'));
    }

    /**
     * Test get attribute from request with defualt value
     * 
     * @return void
     */
    public function testGetAttributeDefualtValue()
    {
        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->withAttribute('foo', 'bar');

        $this->assertEquals('foo', $newRequest->getAttribute('foo2', 'foo'));
    }

    /**
     * Test removing attribute from the server request
     * 
     * @return void
     */
    public function testWithoutAttribute()
    {
        $serverRequest = $this->getServerRequestForTest();
        $newRequest    = $serverRequest->withAttribute('foo', 'bar');

        $this->assertEquals('bar', $newRequest->getAttribute('foo'));
        $this->assertEquals(null, $serverRequest->getAttribute('foo'));

        $newRequestWithout = $newRequest->withoutAttribute('foo');

        $this->assertEquals('bar', $newRequest->getAttribute('foo'));
        $this->assertEquals(null, $newRequestWithout->getAttribute('foo'));
    }

    /**
     * Test get protocol version
     * 
     * @return void
     */
    public function testGetProtocolVersion()
    {
        $serverRequest = $this->getServerRequestForTest();
        $this->assertEquals('1.1', $serverRequest->getProtocolVersion());
    }

    // ------------ PRIVATE METHODS

    /**
     * Get instance of ServerRequest for the test
     *
     * @param string        $body        Body that will be injected into request.
     * @param array         $cookies     Cookies in format compatible with $_COOKIES.
     * @param Headers|array $headersMock Mock of headers object.
     *
     * @return ServerRequest ServerRequest for the test
     */
    private function getServerRequestForTest($body = '', $cookies = [], $headersMock = [])
    {
        $serverGlobals  = ['HTTP_HOST' => 'test.com'];
        $bodyStreamMock = $this->getMockBuilder(Stream::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $bodyStreamMock->method('__toString')->will($this->returnValue($body));

        $requestUri = new Uri(
            'http',
            'www.example.com',
            1111,
            '/path/123',
            'q=abc',
            'fragment',
            'user',
            'password'
        );

        return new ServerRequest(
            'GET',
            $requestUri,
            $bodyStreamMock,
            $headersMock,
            $serverGlobals,
            $cookies,
            [],
            '1.1'
        );
    }
}
