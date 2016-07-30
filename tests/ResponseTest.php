<?php
namespace Test;

// \Http\Message
use Kambo\Http\Message\Response;

/**
 * Unit test for the Response object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get status code
     * 
     * @return void
     */
    public function testGetStatusCode()
    {
        $serverResponse = new Response();
        $this->assertEquals(200, $serverResponse->getStatusCode());
    }

    /**
     * Test changing status code, operation must be immutable - a new instance of object 
     * must be created and previous instance must retain its value.
     * 
     * @return void
     */
    public function testWithStatus()
    {
        $serverResponse = new Response();
        $withNewStatus  = $serverResponse->withStatus(418);

        $this->assertEquals(200, $serverResponse->getStatusCode());
        $this->assertEquals(418, $withNewStatus->getStatusCode());
    }

    /**
     * Test changing status code and readon phrase, operation must be immutable - a new
     * instance of object must be created and previous instance must retain its value.
     * 
     * @return void
     */
    public function testWithStatusCustom()
    {
        $serverResponse = new Response();
        $withNewStatus  = $serverResponse->withStatus(419, 'Authentication Timeout');

        $this->assertEquals(200, $serverResponse->getStatusCode());
        $this->assertEquals(419, $withNewStatus->getStatusCode());
    }

    /**
     * Test with status method, with invalid reponse code
     * An exception must be thrown.
     * 
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testWithStatusInvalid()
    {
        $serverResponse = new Response();
        $withNewStatus  = $serverResponse->withStatus(999);
    }

    /**
     * Test get reason phrase - defualt value is OK for 
     * the defualt status code 200
     * 
     * @return void
     */
    public function testGetReasonPhrase()
    {
        $serverResponse = new Response();
        $this->assertEquals('OK', $serverResponse->getReasonPhrase());
    }

    /**
     * Test get reason phrase for custom response code - empty
     * string is returned
     * 
     * @return void
     */
    public function testGetReasonPhraseEmpty()
    {
        $serverResponse = new Response(520);
        $this->assertEquals('', $serverResponse->getReasonPhrase());
    }

    /**
     * Test get reason phrase for custom response code and reason phrase - same
     * reason phrase must be returne.
     * 
     * @return void
     */
    public function testGetReasonPhraseCustom()
    {
        $serverResponse = new Response();
        $withNewStatus  = $serverResponse->withStatus(419, 'Authentication Timeout');

        $this->assertEquals('Authentication Timeout', $withNewStatus->getReasonPhrase());
    }
}
