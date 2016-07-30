<?php
namespace Test\Factories\Superglobal;

// \Http\Message
use Kambo\Http\Message\Headers;
use Kambo\Http\Message\Environment\Environment;
use Kambo\Http\Message\Factories\Environment\Superglobal\HeadersFactory;

/**
 * Unit test for the HeadersFactory object.
 *
 * @package Test\Factories\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class HeadersFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creating headers from environment object.
     * 
     * @return void
     */
    public function testCreate()
    {
        $serverSuperglobal = [
            'HTTP_HOST' => 'test.com',
            'REQUEST_SCHEME' => 'http',
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

        $expectedHeaders = [
            'host' => [
                'test.com',
            ],
            'php-auth-user' => [
                'user',
            ],
            'php-auth-pw' => [
                'password',
            ],
        ];

        $headers = (new HeadersFactory())->create($this->getEnvironmentMock($serverSuperglobal));

        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertEquals($expectedHeaders, $headers->all());
    }

    /**
     * Test creating headers from environment object with redirect headers.
     * 
     * @return void
     */
    public function testCreateWithRedirect()
    {
        $serverSuperglobal = [
            'HTTP_HOST' => 'test.com',
            'REDIRECT_PHP_AUTH_USER' => 'user',
            'REDIRECT_PHP_AUTH_PW' => 'password',
        ];

        $expectedHeaders = [
            'host' => [
                'test.com',
            ],
            'php-auth-user' => [
                'user',
            ],
            'php-auth-pw' => [
                'password',
            ],
        ];

        $headers = (new HeadersFactory())->create($this->getEnvironmentMock($serverSuperglobal));

        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertEquals($expectedHeaders, $headers->all());
    }

    /**
     * Test creating headers from environment object with redirect headers - Non-prefixed
     * versions must be preferred.
     * 
     * @return void
     */
    public function testCreateWithRedirectOrder()
    {
        $serverSuperglobal = [
            'REDIRECT_HTTP_HOST' => 'prefixed',
            'HTTP_HOST' => 'nonprefixed',
            'PHP_AUTH_USER' => 'nonprefixed',
            'REDIRECT_PHP_AUTH_USER' => 'prefixed',
            'REDIRECT_PHP_AUTH_PW' => 'prefixed',
        ];

        $expectedHeaders = [
            'host' => [
                'nonprefixed',
            ],
            'php-auth-user' => [
                'nonprefixed',
            ],
            'php-auth-pw' => [
                'prefixed',
            ],
        ];

        $headers = (new HeadersFactory())->create($this->getEnvironmentMock($serverSuperglobal));

        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertEquals($expectedHeaders, $headers->all());
    }

    /**
     * Get instance of mocked Environment object for testing purpose.
     *
     * @param array $serverSuperglobal array in same format as server superglobal
     *                                 variable ($_SERVER).
     *
     * @return Environment
     */
    private function getEnvironmentMock(array $serverSuperglobal = [])
    {
        $environmentMock = $this->getMockBuilder(Environment::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $environmentMock->method('getServer')->will(
            $this->returnValue($serverSuperglobal)
        );

        return $environmentMock;
    }
}
