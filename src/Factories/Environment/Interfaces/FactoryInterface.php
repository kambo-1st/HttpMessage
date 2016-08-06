<?php
namespace Kambo\Http\Message\Factories\Environment\Interfaces;

// \Http\Message
use Kambo\Http\Message\Environment\Environment;

/**
 * Factory interface
 *
 * @package Kambo\Http\Message\Factories\Environment\Interfaces
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
interface FactoryInterface
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
