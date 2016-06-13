<?php
namespace Test;

// \HttpMessage
use Kambo\HttpMessage\Enviroment\Enviroment;

/**
 * Unit test for the enviroment object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class EnviromentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get query string from the enviroment
     * 
     * @return void
     */
    public function testGetQueryString()
    {
        $this->assertEquals('q=abc', $this->getTestObject()->getQueryString());
    }

    /**
     * Test get request method from the enviroment
     * 
     * @return void
     */
    public function testGetRequestMethod()
    {
        $this->assertEquals('GET', $this->getTestObject()->getRequestMethod());
    }

    /**
     * Test get request uri from the enviroment
     * 
     * @return void
     */
    public function testGetRequestUri()
    {
        $this->assertEquals('/path/123?q=abc', $this->getTestObject()->getRequestUri());
    }

    /**
     * Test get request scheme from the enviroment
     * 
     * @return void
     */
    public function testGetRequestScheme()
    {
        $this->assertEquals('http', $this->getTestObject()->getRequestScheme());
    }

    /**
     * Test get host from the enviroment
     * 
     * @return void
     */
    public function testGetHost()
    {
        $this->assertEquals('test.com', $this->getTestObject()->getHost());
    }

    /**
     * Test get port from the enviroment
     * 
     * @return void
     */
    public function testGetPort()
    {
        $this->assertEquals('1111', $this->getTestObject()->getPort());
    }

    /**
     * Test get protocol version from the enviroment
     * 
     * @return void
     */
    public function testGetProtocolVersion()
    {
        $this->assertEquals('1.1', $this->getTestObject()->getProtocolVersion());
    }

    /**
     * Test get auth user from the enviroment
     * 
     * @return void
     */
    public function testGetAuthUser()
    {
        $this->assertEquals('user', $this->getTestObject()->getAuthUser());
    }

    /**
     * Test get auth password from the enviroment
     * 
     * @return void
     */
    public function testGetAuthPassword()
    {
        $this->assertEquals('password', $this->getTestObject()->getAuthPassword());
    }

    /**
     * Test get server variable from the enviroment
     * 
     * @return void
     */
    public function testGetServer()
    {
        $expectedServer = [
            'HTTP_HOST' => 'test.com',
            'SERVER_NAME' => 'test.com',
            'SERVER_ADDR' => '10.0.2.15',
            'SERVER_PORT' => '1111',
            'REMOTE_ADDR' => '10.0.2.2',
            'REQUEST_SCHEME' => 'http',
            'REMOTE_PORT' => '64267',
            'REDIRECT_QUERY_STRING' => 'q=abc',
            'REDIRECT_URL' => '/path/123',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'q=abc',
            'REQUEST_URI' => '/path/123?q=abc',
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'password',
        ];

        $this->assertEquals($expectedServer, $this->getTestObject()->getServer());
    }

    /**
     * Test get request body from the enviroment
     * 
     * @return void
     */
    public function testGetBody()
    {
        $this->assertEquals('body', $this->getTestObject()->getBody());
    }

    /**
     * Test get cookies from the enviroment
     * 
     * @return void
     */
    public function testGetCookies()
    {
        $this->assertEquals(['cookies'], $this->getTestObject()->getCookies());
    }

    /**
     * Test get files from the enviroment
     * 
     * @return void
     */
    public function testGetFiles()
    {
        $this->assertEquals(['files'], $this->getTestObject()->getFiles());
    }

    /**
     * Get instance of Enviroment for test with preset values.
     * 
     * @return Enviroment instance of Enviroment for test
     */
    private function getTestObject()
    {
        $server = [
            'HTTP_HOST' => 'test.com',
            'SERVER_NAME' => 'test.com',
            'SERVER_ADDR' => '10.0.2.15',
            'SERVER_PORT' => '1111',
            'REMOTE_ADDR' => '10.0.2.2',
            'REQUEST_SCHEME' => 'http',
            'REMOTE_PORT' => '64267',
            'REDIRECT_QUERY_STRING' => 'q=abc',
            'REDIRECT_URL' => '/path/123',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'q=abc',
            'REQUEST_URI' => '/path/123?q=abc',
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'password',
        ];

        return new Enviroment($server, ['cookies'], ['files'], 'body');
    }
}
