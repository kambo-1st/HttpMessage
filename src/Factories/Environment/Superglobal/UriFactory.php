<?php
namespace Kambo\HttpMessage\Factories\Environment\Superglobal;

// \HttpMessage
use Kambo\HttpMessage\Uri;
use Kambo\HttpMessage\Environment\Environment;
use Kambo\HttpMessage\Factories\Environment\Interfaces\Factory;

/**
 * Create instance of Uri object from instance of Environment object
 *
 * @package Kambo\HttpMessage\Factories\Environment\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriFactory implements Factory
{
    /**
     * Create instances of Uri object from instance of Environment object
     *
     * @param Environment $environment environment data
     *
     * @return Uri Instance of Uri object created from environment
     */
    public function create(Environment $environment)
    {
        $scheme = $environment->getRequestScheme();
        $host   = $environment->getHost();
        $port   = $environment->getPort();

        $path  = parse_url('http://example.com' . $environment->getRequestUri(), PHP_URL_PATH);
        $query = $environment->getQueryString();
        $user  = $environment->getAuthUser();
        $pass  = $environment->getAuthPassword();

        return new Uri($scheme, $host, $port, $path, $query, '', $user, $pass);
    }
}
