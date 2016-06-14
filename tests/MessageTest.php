<?php
namespace Test;

// \Spl
use ReflectionClass;

// \HttpMessage
use Kambo\HttpMessage\Message;
use Kambo\HttpMessage\Headers;
use Kambo\HttpMessage\Stream;

use Kambo\HttpMessage\Enviroment\Enviroment;
use Kambo\HttpMessage\Factories\Enviroment\Superglobal\HeadersFactory;

/**
 * Unit test for the Message object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     *
     * @return void
     */
    public function testGetProtocolVersion()
    {
        $message = $this->getMessageForTest();
        $this->assertEquals('1.0', $message->getProtocolVersion());
    }

    /**
     *
     *
     * @return void
     */
    public function testWithProtocolVersion()
    {
        $message = $this->getMessageForTest();
        $clone   = $message->withProtocolVersion('1.1');

        $this->assertEquals('1.0', $message->getProtocolVersion());
        $this->assertEquals('1.1', $clone->getProtocolVersion());
    }

    /**
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWithProtocolVersionInvalidThrowsException()
    {
        $message = $this->getMessageForTest();
        $message->withProtocolVersion('3.0');
    }

    /**
     *
     *
     * @return void
     */
    public function testGetHeaders()
    {
        $message  = $this->getMessageForTest($this->getHeadersForTest());
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
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
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

        $this->assertEquals($expected, $message->getHeaders());
    }

    /**
     *
     *
     * @return void
     */
    public function testGetHeaderLine()
    {
        $message = $this->getMessageForTest($this->getHeadersForTest());
        $expected = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36'.
        ' (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36';
        $this->assertEquals($expected, $message->getHeaderLine('user-agent'));
    }

    /**
     *
     *
     * @return void
     */
    public function testWithHeader()
    {
        $message  = $this->getMessageForTest($this->getHeadersForTest());
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
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
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

        $newMessageExpected = [
            "host" => [
                "foo.bar"
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
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
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

        $newMessage = $message->withHeader('host', 'foo.bar');

        // has not been changed
        $this->assertEquals($expected, $message->getHeaders());
        $this->assertEquals($newMessageExpected, $newMessage->getHeaders());
    }

    /**
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWithHeaderInvalid()
    {
        $message = $this->getMessageForTest($this->getHeadersForTest());
        $newMessage = $message->withHeader(['host'], 'foo.bar');
    }

    /**
     *
     *
     * @return void
     */
    public function testWithAddedHeader()
    {
        $message  = $this->getMessageForTest($this->getHeadersForTest());
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
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
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

        $newMessageExpected = [
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
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
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
            ],
            "new-header" => [
                "test"
            ]
        ];

        $newMessage = $message->withAddedHeader('New-header', 'test');

        // has not been changed
        $this->assertEquals($expected, $message->getHeaders());
        $this->assertEquals($newMessageExpected, $newMessage->getHeaders());
    }

    /**
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWithAddedHeaderInvalid()
    {
        $message = $this->getMessageForTest($this->getHeadersForTest());
        $newMessage = $message->withAddedHeader(['New-header'], 'test');
    }

    /**
     *
     *
     * @return void
     */
    public function testWithoutHeader()
    {
        $message = $this->getMessageForTest($this->getHeadersForTest());
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
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '.
                'Chrome/47.0.2526.111 Safari/537.36'
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

        $newMessageExpected = [
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

        $newMessage = $message->withoutHeader('user-agent', 'test');

        // has not been changed
        $this->assertEquals($expected, $message->getHeaders());
        $this->assertEquals($newMessageExpected, $newMessage->getHeaders());
    }


    /**
     *
     *
     * @return void
     */
    public function testGetBody()
    {
        $expected = 'test message';
        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($expected);

        $message     = $this->getMessageForTest(null, $body);
        $messageBody = $message->getBody();
        $messageBody->rewind();

        $this->assertEquals($expected, $messageBody->getContents());
    }


    /**
     *
     *
     * @return void
     */
    public function testWithBody()
    {
        $expected = 'test message';
        $expectedChange = 'test change';

        $newBody = new Stream(fopen('php://temp', 'r+'));
        $newBody->write($expectedChange);

        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($expected);

        $message = $this->getMessageForTest(null, $body);

        $messageNewBody = $message->withBody($newBody);
        $messageNewBody = $messageNewBody->getBody();
        $messageNewBody->rewind();

        $messageBody = $message->getBody();
        $messageBody->rewind();

        $this->assertEquals($expected, $messageBody->getContents());
        $this->assertEquals($expectedChange, $messageNewBody->getContents());
    }

    // ------------ PRIVATE METHODS

    /**
     *
     *
     * @return Message Instance of message for the testing purpose
     */
    private function getMessageForTest($headers = null, $body = null)
    {
        $messageStub = new Message();

        $reflectionClass    = new ReflectionClass('Kambo\HttpMessage\Message');
        $reflectionProperty = $reflectionClass->getProperty('protocolVersion');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($messageStub, '1.0');

        if (isset($headers)) {
            $reflectionProperty = $reflectionClass->getProperty('headers');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($messageStub, $headers);
        }

        if (isset($body)) {
            $reflectionProperty = $reflectionClass->getProperty('body');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($messageStub, $body);
        }

        return $messageStub;
    }

    /**
     *
     *
     * @return Headers Instance of message for the testing purpose
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

        $enviroment = new Enviroment($headersForTest);
        return HeadersFactory::fromEnviroment($enviroment);
    }
}
