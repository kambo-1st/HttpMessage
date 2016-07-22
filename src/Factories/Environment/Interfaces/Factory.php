<?php
namespace Kambo\HttpMessage\Factories\Environment\Interfaces;

// \HttpMessage
use Kambo\HttpMessage\Environment\Environment;

/**
 * Factory interface
 *
 * @package Kambo\HttpMessage\Factories\Environment\Interfaces
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
interface Factory
{
    /**
     * Create instances of objects from Environment
     *
     * @param Environment $environment environment data
     *
     * @return mixed instance of object based on Environment 
     */
    public function create(Environment $environment);
}
