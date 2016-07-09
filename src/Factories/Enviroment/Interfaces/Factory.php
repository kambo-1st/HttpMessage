<?php
namespace Kambo\HttpMessage\Factories\Enviroment\Interfaces;

// \HttpMessage
use Kambo\HttpMessage\Enviroment\Enviroment;

/**
 * Factory interface
 *
 * @package Kambo\HttpMessage\Factories\Enviroment\Interfaces
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
interface Factory
{
    /**
     * Create instances of objects from Enviroment
     *
     * @param Enviroment $enviroment enviroment data
     *
     * @return mixed instance of object based on Enviroment 
     */
    public function create(Enviroment $enviroment);
}
