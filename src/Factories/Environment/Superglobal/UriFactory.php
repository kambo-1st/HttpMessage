<?php
namespace Kambo\Http\Message\Factories\Environment\Superglobal;

// \Spl
use InvalidArgumentException;

// \Http\Message
use Kambo\Http\Message\Uri;
use Kambo\Http\Message\Environment\Environment;
use Kambo\Http\Message\Factories\Environment\Interfaces\FactoryInterface;

/**
 * Create instance of Uri object from instance of Environment object
 *
 * @package Kambo\Http\Message\Factories\Environment\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriFactory implements FactoryInterface
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
        $query  = $environment->getQueryString();
        $user   = $environment->getAuthUser();
        $pass   = $environment->getAuthPassword();

        // parse_url() requires a full URL - but only URL path is need it here.
        $path = parse_url('http://example.com' . $environment->getRequestUri(), PHP_URL_PATH);
        if ($path === false) {
            throw new InvalidArgumentException('Uri path must be a string');
        }

        return new Uri($scheme, $host, $port, $path, $query, '', $user, $pass);
    }
}
