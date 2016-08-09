<?php
namespace Test;

// \Spl
use ReflectionClass;

// \Http\Message
use Kambo\Http\Message\Message;
use Kambo\Http\Message\Headers;
use Kambo\Http\Message\Stream;

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
     * Test create instance of Message
     *
     * @return void
     */
    public function testCreate()
    {
        $message = new Message();
        $this->assertInstanceOf(Message::class, $message);
    }

    /**
     * Test create instance of Message
     *
     * @expectedException \InvalidArgumentException
     * @return void
     */
    public function testCreateInstanceInvalidParameters()
    {
        $message = new Message('invalid headers');
    }

    /**
     * Test get protocol version.
     *
     * @return void
     */
    public function testGetProtocolVersion()
    {
        $message = $this->getMessageForTest();
        $this->assertEquals('1.0', $message->getProtocolVersion());
    }

    /**
     * Test setting new protocol version.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
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
     * Test setting wrong protocol version - an exception should be raised.
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
     * Test get all headers.
     *
     * @return void
     */
    public function testGetHeaders()
    {
        $serverTest = [
            'HTTP_HOST' => 'test.com',
            'HTTP_USER_AGENT' =>
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '.
                'Chrome/47.0.2526.111 Safari/537.36'
        ];

        $message  = $this->getMessageForTest($serverTest);
        $expected = [
            "host" => [
                "test.com"
            ],
            "user-agent" => [
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
            ],
        ];

        $this->assertEquals($expected, $message->getHeaders());
    }

    /**
     * Test get one header line.
     *
     * @return void
     */
    public function testGetHeaderLine()
    {
        $serverTest = [
            'HTTP_USER_AGENT' =>
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '.
                'Chrome/47.0.2526.111 Safari/537.36'
        ];

        $message = $this->getMessageForTest($serverTest);
        $expected = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36'.
        ' (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36';
        $this->assertEquals($expected, $message->getHeaderLine('user-agent'));
    }

    /**
     * Test changing header.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithHeader()
    {
        $serverTest = [
            'HTTP_HOST' => 'test.com',
            'HTTP_USER_AGENT' =>
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '.
                'Chrome/47.0.2526.111 Safari/537.36'
        ];

        $message  = $this->getMessageForTest($serverTest);
        $expected = [
            "host" => [
                "test.com"
            ],
            "user-agent" => [
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
            ],
        ];

        $newMessageExpected = [
            "host" => [
                "foo.bar"
            ],
            "user-agent" => [
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
            ],
        ];

        $newMessage = $message->withHeader('host', 'foo.bar');

        // has not been changed
        $this->assertEquals($expected, $message->getHeaders());
        $this->assertEquals($newMessageExpected, $newMessage->getHeaders());
    }

    /**
     * Test with invalid header - an exception should be raised.
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWithHeaderInvalid()
    {
        $message = $this->getMessageForTest();
        $newMessage = $message->withHeader(['host'], 'foo.bar');
    }

    /**
     * Test with added header method - adding new "New-header" header.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithAddedHeader()
    {
        $serverTest = [
            'HTTP_HOST' => 'test.com',
            'HTTP_USER_AGENT' =>
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '.
                'Chrome/47.0.2526.111 Safari/537.36'
        ];

        $message  = $this->getMessageForTest($serverTest);
        $expected = [
            "host" => [
                "test.com"
            ],
            "user-agent" => [
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
            ],
        ];

        $newMessageExpected = [
            "host" => [
                "test.com"
            ],
            "user-agent" => [
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 '.
                '(KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
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
     * Test with added header method -  an exception should be raised.
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWithAddedHeaderInvalid()
    {
        $message = $this->getMessageForTest();
        $newMessage = $message->withAddedHeader(['New-header'], 'test');
    }

    /**
     * Test removing header - header 'user-agent' will be removed.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithoutHeader()
    {
        $serverTest = [
            'HTTP_HOST' => 'test.com',
            'HTTP_USER_AGENT' =>
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '.
                'Chrome/47.0.2526.111 Safari/537.36'
        ];

        $message = $this->getMessageForTest($serverTest);

        $expected = [
            "host" => [
                "test.com"
            ],
            "user-agent" => [
                'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '.
                'Chrome/47.0.2526.111 Safari/537.36'
            ],
        ];

        $newMessageExpected = [
            "host" => [
                "test.com"
            ],
        ];

        $newMessage = $message->withoutHeader('user-agent', 'test');

        // has not been changed
        $this->assertEquals($expected, $message->getHeaders());
        $this->assertEquals($newMessageExpected, $newMessage->getHeaders());
    }

    /**
     * Test get body.
     *
     * @return void
     */
    public function testGetBody()
    {
        $expected = 'test message';
        $body     = new Stream(fopen('php://temp', 'r+'));
        $body->write($expected);

        $message     = $this->getMessageForTest(null, $body);
        $messageBody = $message->getBody();
        $messageBody->rewind();

        $this->assertEquals($expected, $messageBody->getContents());
    }

    /**
     * Test setting new body.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithBody()
    {
        $expected       = 'test message';
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
     * Get instance of message object for the test.
     *
     * @param array|null  $headers Http headers in same format as in $_SERVER superglobal.
     *                             Value will be injected into testing instance.
     * @param Stream|null $name    Message body that will be injected into testing instance.
     *
     * @return Message Instance of message for the testing purpose
     */
    private function getMessageForTest($headers = null, $body = null)
    {
        $messageStub = new Message();

        $reflectionClass    = new ReflectionClass(Message::class);
        $reflectionProperty = $reflectionClass->getProperty('protocolVersion');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($messageStub, '1.0');

        if (isset($headers)) {
            $headersStub        = new Headers($headers);
            $reflectionProperty = $reflectionClass->getProperty('headers');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($messageStub, $headersStub);
        }

        if (isset($body)) {
            $reflectionProperty = $reflectionClass->getProperty('body');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($messageStub, $body);
        }

        return $messageStub;
    }
}
