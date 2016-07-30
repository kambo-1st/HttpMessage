<?php
namespace Test;

// \Http\Message
use Kambo\Http\Message\Headers;

/**
 * Unit test for the Headers object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class HeadersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get all headers in object
     * 
     * @return void
     */
    public function testGetAll()
    {
        $headers  = $this->getHeadersForTest();
        $expected = [
            "host" => [
                "test.com"
            ],
            "connection" => [
                "keep-alive"
            ],
            "cache-control" => [
                "max-age=0"
            ],
            "accept" => [
                "text/html",
                "application/xhtml+xml",
                "application/xml;q=0.9",
                "image/webp",
                "*/*;q=0.8"
            ],
            "upgrade-insecure-requests" => [
                "1"
            ],
            "user-agent" => [
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36'.
                ' (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
            ],
            "dnt" => [
                "1"
            ],
            "accept-encoding" => [
                "gzip",
                "deflate",
                "sdch"
            ],
            "accept-language" => [
                "cs-CZ",
                "cs;q=0.8",
                "en;q=0.6"
            ],
            "php-auth-user" => [
                "user"
            ],
            "php-auth-pw" => [
                "password"
            ]
        ];

        $this->assertEquals($expected, $headers->all());
    }

    /**
     * Test get particular header by its name
     * 
     * @return void
     */
    public function testGet()
    {
        $headers   = $this->getHeadersForTest();
        $userAgent = $headers->get('User-Agent');
        $expected  = [
            'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '.
             'Chrome/47.0.2526.111 Safari/537.36'
        ];

        $this->assertEquals($expected, $userAgent);
    }

    /**
     * Test get particular header values separated by comma
     * 
     * @return void
     */
    public function testGetLine()
    {
        $headers  = $this->getHeadersForTest();
        $accept   = $headers->getLine('accept');
        $expected = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';

        $this->assertEquals($expected, $accept);
    }

    /**
     * Test if particular header line exist
     * 
     * @return void
     */
    public function testExists()
    {
        $headers = $this->getHeadersForTest();

        $this->assertTrue($headers->exists('accept-language'));
    }

    /**
     * Test removing of particular header by its name
     * 
     * @return void
     */
    public function testRemove()
    {
        $headers = $this->getHeadersForTest();
        $headers->remove('accept-language');

        $this->assertFalse($headers->exists('accept-language'));
    }

    /**
     * Test setting header by specified name.
     * If the header already exist value must be replaced.
     * 
     * @return void
     */
    public function testSet()
    {
        $headers = $this->getHeadersForTest();
        $headers->set('HOST', ['testnew.com']);
        $newHost = $headers->get('host');

        $this->assertEquals(['testnew.com'], $newHost);
    }

    /**
     * Test setting header by specified name.
     * If the header already exist value must be merged.
     * 
     * @return void
     */
    public function testAdd()
    {
        $headers = $this->getHeadersForTest();
        $headers->add('accept-encoding', 'compress');
        $expected = [
            'gzip',
            'deflate',
            'sdch',
            'compress'
        ];

        $acceptEncoding = $headers->get('accept-encoding');

        $this->assertEquals($expected, $acceptEncoding);
    }

    /**
     * Get instance of Headers for the testing
     * 
     * @return Headers 
     */
    private function getHeadersForTest()
    {
        $headersForTest = [
            'HTTP_HOST' => 'test.com',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) '.
            'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
            'HTTP_DNT' => '1',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'cs-CZ,cs;q=0.8,en;q=0.6',
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'password'
        ];
        
        return new Headers($headersForTest);
    }
}
