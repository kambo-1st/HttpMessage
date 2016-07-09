<?php
namespace Kambo\HttpMessage\Factories\Enviroment\Superglobal;

// \HttpMessage
use Kambo\HttpMessage\Headers;
use Kambo\HttpMessage\Enviroment\Enviroment;
use Kambo\HttpMessage\Factories\Enviroment\Interfaces\Factory;

/**
 * Create instance of Headers object from instance of Enviroment object
 *
 * @package Kambo\HttpMessage\Factories\Enviroment\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class HeadersFactory implements Factory
{
    /**
     * Special HTTP headers without "HTTP_" prefix for resolving headers
     *
     * @var array
     */
    private $specialHeaders = [
        'CONTENT_TYPE' => true,
        'CONTENT_LENGTH' => true,
        'PHP_AUTH_USER' => true,
        'PHP_AUTH_PW' => true,
        'PHP_AUTH_DIGEST' => true,
        'AUTH_TYPE' => true,
    ];

    /**
     * Create instance of Headers object from instance of Enviroment object
     *
     * @param Enviroment $enviroment enviroment data
     *
     * @return Headers Instance of Headers object from enviroment
     */
    public function create(Enviroment $enviroment)
    {
        return new Headers($this->resolveHeaders($enviroment->getServer()));
    }

    /**
     * Resolve headers from provided array
     *
     * @param array $headersForResolve array compatible with $_SERVER superglobal variable
     *
     * @return Headers Instance of Headers object from enviroment
     */
    private function resolveHeaders($headersForResolve)
    {
        $headers = [];

        foreach ($headersForResolve as $name => $value) {
            if (strpos($name, 'REDIRECT_') === 0) {
                $name = substr($name, 9);

                // Do not replace existing variables
                if (array_key_exists($name, $headersForResolve)) {
                     continue;
                }
            }

            if (substr($name, 0, 5) == 'HTTP_' || isset($this->specialHeaders[$name])) {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
