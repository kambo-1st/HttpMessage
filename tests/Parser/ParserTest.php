<?php
namespace Test\Parser;

// \HttpMessage
use Kambo\HttpMessage\Parser\Parser;

/**
 * Unit test for the Parser object.
 *
 * @package Test\Parser
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parsing JSON
     * 
     * @return void
     */
    public function testParseJson()
    {
        $expected = [
            "test" => "test",
            "submit" => "Test"
        ];

        $parseDefault = new Parser('application/json');
        $this->assertEquals($expected, $parseDefault->parse(json_encode($expected)));
    }

    /**
     * Test parsing XML
     * 
     * @return void
     */
    public function testParseXml()
    {
        $expected = [
            "test" => "data",
            "example" => "here"
        ];

        $bodyXml = "<?xml version='1.0'?> 
        <document>
         <test>data</test>
         <example>here</example>
        </document>";

        $parseDefault = new Parser('text/xml');
        $this->assertEquals($expected, (array)$parseDefault->parse($bodyXml));
    }

    /**
     * Test parsing form data.
     * 
     * @return void
     */
    public function testParseFormData()
    {
        $expected = [
            'test' => 'test',
            'submit' => 'Test'
        ];
        $parseDefault = new Parser('application/x-www-form-urlencoded');
        $this->assertEquals($expected, $parseDefault->parse('test=test&submit=Test'));
    }

    /**
     * Test parsing default.
     * If the data type is not support input data are returned without any modification.
     * 
     * @return void
     */
    public function testParseDefault()
    {
        $parseDefault = new Parser('');
        $this->assertEquals('foo', $parseDefault->parse('foo'));
    }
}
