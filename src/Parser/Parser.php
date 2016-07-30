<?php

namespace Kambo\Http\Message\Parser;

/**
 * Parse input data according their type
 */
class Parser
{
    /**
     * Data type
     *
     * @var string
     */
    private $type;

    /**
     * Object constructor
     *
     * @param string $type data type for parsing a proper parsing method will be used
     *                     according this value.
     *
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Parse data according their type.
     *
     * @param mixed $data data for parsing
     *
     * @return mixed Type of returned valus is based on type of data if the type is XML 
     *               an instance of SimpleXMLElement is returned, else an array is returned
     *               If the data type is not support a null is returned.
     */
    public function parse($data)
    {
        $parsedData = null;
        switch ($this->type) {
            case 'application/json':
                $parsedData = json_decode($data, true);
                break;
            case 'application/xml':
            case 'text/xml':
                $backup     = libxml_disable_entity_loader(true);
                $parsedData = simplexml_load_string($data);
                libxml_disable_entity_loader($backup);
                break;
            case 'application/x-www-form-urlencoded':
            case 'multipart/form-data':
                parse_str($data, $parsedData);
                break;
            default:
                $parsedData = null;
                break;
        }

        return $parsedData;
    }
}
