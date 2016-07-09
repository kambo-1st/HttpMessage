<?php
namespace Kambo\HttpMessage\Factories\Enviroment\Superglobal;

// \HttpMessage
use Kambo\HttpMessage\Uri;
use Kambo\HttpMessage\Enviroment\Enviroment;
use Kambo\HttpMessage\Factories\Enviroment\Interfaces\Factory;

/**
 * Create instance of Uri object from instance of Enviroment object
 *
 * @package Kambo\HttpMessage\Factories\Enviroment\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriFactory implements Factory
{
    /**
     * Create instances of Uri object from instance of Enviroment object
     *
     * @param Enviroment $enviroment enviroment data
     *
     * @return Uri Instance of Uri object created from enviroment
     */
    public function create(Enviroment $enviroment)
    {
        $scheme = $enviroment->getRequestScheme();
        $host   = $enviroment->getHost();
        $port   = $enviroment->getPort();

        $path  = parse_url('http://example.com' . $enviroment->getRequestUri(), PHP_URL_PATH);
        $query = $enviroment->getQueryString();
        $user  = $enviroment->getAuthUser();
        $pass  = $enviroment->getAuthPassword();

        return new Uri($scheme, $host, $port, $path, $query, '', $user, $pass);
    }
}
