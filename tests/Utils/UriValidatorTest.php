<?php
namespace Test\Utils;

// \Http\Message
use Kambo\Http\Message\Utils\UriValidator;

/**
 * Unit test for the UriValidator object.
 *
 * @package Test\Parser
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test validate port
     *
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testValidatePortLowPortNumber()
    {
        $uriValidator = new UriValidator();
        $uriValidator->validatePort(-5);
    }

    /**
     * Test validate port
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testValidatePortHighPortNumber()
    {
        $uriValidator = new UriValidator();
        $uriValidator->validatePort('string');
    }

    /**
     * Test validate port
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testValidatePortString()
    {
        $uriValidator = new UriValidator();
        $uriValidator->validatePort('string');
    }

    /**
     * Test validate port
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testValidatePathArray()
    {
        $uriValidator = new UriValidator();
        $uriValidator->validatePath([]);
    }

    /**
     * Test validate port
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testValidatePathQueryString()
    {
        $uriValidator = new UriValidator();
        $uriValidator->validatePath('/foo?bar');
    }

    /**
     * Test validate port
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testValidatePathURIFragment()
    {
        $uriValidator = new UriValidator();
        $uriValidator->validatePath('/foo#bar');
    }

    /**
     * Test validate port
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testValidateQueryArray()
    {
        $uriValidator = new UriValidator();
        $uriValidator->validateQuery([]);
    }

    /**
     * Test validate port
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testValidateQueryFragment()
    {
        $uriValidator = new UriValidator();
        $uriValidator->validateQuery('/foo#bar');
    }
}
