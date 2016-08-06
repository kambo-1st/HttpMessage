<?php
namespace Kambo\Http\Message\Factories\Environment\Superglobal;

// \Http\Message
use Kambo\Http\Message\Headers;
use Kambo\Http\Message\Environment\Environment;
use Kambo\Http\Message\Factories\Environment\Interfaces\FactoryInterface;

/**
 * Create instance of Headers object from instance of Environment object
 *
 * @package Kambo\Http\Message\Factories\Environment\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class HeadersFactory implements FactoryInterface
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
     * Create instance of Headers object from instance of Environment object
     *
     * @param Environment $environment environment data
     *
     * @return Headers Instance of Headers object from environment
     */
    public function create(Environment $environment)
    {
        return new Headers($this->resolveHeaders($environment->getServer()));
    }

    /**
     * Resolve headers from provided array
     *
     * @param array $headersForResolve array compatible with $_SERVER superglobal variable
     *
     * @return Headers Instance of Headers object from environment
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
