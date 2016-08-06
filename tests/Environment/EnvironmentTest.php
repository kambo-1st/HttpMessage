<?php
namespace Test;

// \Http\Message
use Kambo\Http\Message\Environment\Environment;

/**
 * Unit test for the environment object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get query string from the environment
     * 
     * @return void
     */
    public function testGetQueryString()
    {
        $testedObject = $this->getTestObject(['QUERY_STRING' => 'q=abc']);
        $this->assertEquals('q=abc', $testedObject->getQueryString());
    }

    /**
     * Test get query string from the environment if the query string is not provided.
     * 
     * @return void
     */
    public function testGetQueryStringNotProvided()
    {
        $testedObject = $this->getTestObject();
        $this->assertEquals(null, $testedObject->getQueryString());
    }

    /**
     * Test get request method from the environment
     * 
     * @return void
     */
    public function testGetRequestMethod()
    {
        $testedObject = $this->getTestObject(['REQUEST_METHOD' => 'GET']);
        $this->assertEquals('GET', $testedObject->getRequestMethod());
    }

    /**
     * Test get request method from the environment if the request method is not provided.
     * 
     * @return void
     */
    public function testGetRequestMethodNotProvided()
    {
        $testedObject = $this->getTestObject();
        $this->assertEquals(null, $testedObject->getRequestMethod());
    }

    /**
     * Test get request uri from the environment
     * 
     * @return void
     */
    public function testGetRequestUri()
    {
        $testedObject = $this->getTestObject(['REQUEST_URI' => '/path/123?q=abc']);
        $this->assertEquals('/path/123?q=abc', $testedObject->getRequestUri());
    }

    /**
     * Test get request uri from the environment if the request uri is not provided.
     * 
     * @return void
     */
    public function testGetRequestUriNotProvided()
    {
        $testedObject = $this->getTestObject();
        $this->assertEquals(null, $testedObject->getRequestUri());
    }

    /**
     * Test get request scheme from the environment
     * 
     * @return void
     */
    public function testGetRequestScheme()
    {
        $testedObject = $this->getTestObject(['REQUEST_SCHEME' => 'http']);
        $this->assertEquals('http', $testedObject->getRequestScheme());
    }

    /**
     * Test get request scheme from the environment if the request scheme is not provided.
     * 
     * @return void
     */
    public function testGetRequestSchemeNotProvided()
    {
        $testedObject = $this->getTestObject();
        $this->assertEquals(null, $testedObject->getRequestScheme());
    }

    /**
     * Test get host from the environment
     * 
     * @return void
     */
    public function testGetHost()
    {
        $testedObject = $this->getTestObject(['HTTP_HOST' => 'test.com']);
        $this->assertEquals('test.com', $testedObject->getHost());
    }

    /**
     * Test get host from the environment if the host is not provided.
     * 
     * @return void
     */
    public function testGetHostNotProvided()
    {
        $testedObject = $this->getTestObject();
        $this->assertEquals(null, $testedObject->getHost());
    }

    /**
     * Test get port from the environment
     *
     * @return void
     */
    public function testGetPort()
    {
        $testedObject = $this->getTestObject(['SERVER_PORT' => '1111']);
        $this->assertEquals('1111', $testedObject->getPort());
    }

    /**
     * Test get port from the environment if the port is not provided.
     *
     * @return void
     */
    public function testGetPortNotProvided()
    {
        $testedObject = $this->getTestObject();
        $this->assertEquals(null, $testedObject->getPort());
    }

    /**
     * Test get protocol version from the environment
     *
     * @return void
     */
    public function testGetProtocolVersion()
    {
        $testedObject = $this->getTestObject(['SERVER_PROTOCOL' => 'HTTP/1.1']);
        $this->assertEquals('1.1', $testedObject->getProtocolVersion());
    }

    /**
     * Test get protocol version from the environment if the protocol version
     * is not provided.
     *
     * @return void
     */
    public function testGetProtocolVersionNotProvided()
    {
        $testedObject = $this->getTestObject();
        $this->assertEquals(null, $testedObject->getProtocolVersion());
    }

    /**
     * Test get auth user from the environment
     * 
     * @return void
     */
    public function testGetAuthUser()
    {
        $this->assertEquals(
            'user',
            $this->getTestObject(['PHP_AUTH_USER' => 'user'])->getAuthUser()
        );
    }

    /**
     * Test get auth password from the environment
     * 
     * @return void
     */
    public function testGetAuthPassword()
    {
        $this->assertEquals(
            'password',
            $this->getTestObject(['PHP_AUTH_PW' => 'password'])->getAuthPassword()
        );
    }

    /**
     * Test get auth user from the environment - auth user was not provided.
     * 
     * @return void
     */
    public function testGetAuthUserNotProvided()
    {
        $this->assertEquals(
            null,
            $this->getTestObject()->getAuthUser()
        );
    }

    /**
     * Test get auth password from the environment - password was not provided.
     * 
     * @return void
     */
    public function testGetAuthPasswordNotProvided()
    {
        $this->assertEquals(
            null,
            $this->getTestObject()->getAuthPassword()
        );
    }

    /**
     * Test get server variable from the environment
     * 
     * @return void
     */
    public function testGetServer()
    {
        $expectedServer = [
            'HTTP_HOST' => 'test.com',
            'SERVER_NAME' => 'test.com',
            'SERVER_ADDR' => '10.0.2.15'
        ];

        $this->assertEquals($expectedServer, $this->getTestObject($expectedServer)->getServer());
    }

    /**
     * Test get request body from the environment
     * 
     * @return void
     */
    public function testGetBody()
    {
        $this->assertInternalType('resource', $this->getTestObject()->getBody());
    }

    /**
     * Test creating Environment with invalid body parameter - body must be resource.
     *
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testGetBodyInvalid()
    {
        return new Environment([], 'invalid input');
    }

    /**
     * Test get post parameters from the environment
     *
     * @return void
     */
    public function testGetPost()
    {
        $this->assertEquals(['post'], $this->getTestObject()->getPost());
    }

    /**
     * Test get cookies from the environment
     * 
     * @return void
     */
    public function testGetCookies()
    {
        $this->assertEquals(['cookies'], $this->getTestObject()->getCookies());
    }

    /**
     * Test get files from the environment
     * 
     * @return void
     */
    public function testGetFiles()
    {
        $this->assertEquals(['files'], $this->getTestObject()->getFiles());
    }

    /**
     * Get instance of Environment for test with preset values.
     *
     * @param array $additionalValues Uri scheme.
     *
     * @return Environment instance of Environment for test
     */
    private function getTestObject(array $server = [])
    {
        return new Environment($server, fopen('php://memory', 'r+'), ['post'], ['cookies'], ['files']);
    }
}
