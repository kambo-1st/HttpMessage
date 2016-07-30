<?php
namespace Kambo\Http\Message\Factories\String;

// \Http\Message
use Kambo\Http\Message\Uri;

/**
 * Create instances of Uri object from the string
 *
 * @package Kambo\Http\Message\Factories\String
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriFactory
{
    /**
     * Create new Uri from provided string.
     *
     * @param string $uri uri that will be parsed into URI object
     *
     * @return Uri Instance of Uri based on provided string
     */
    public function create($uri)
    {
        list($scheme, $host, $port, $path, $query, $fragment, $user, $pass) = $this->parseUrl($uri);

        return new Uri($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }

    /**
     * Parse uri to array with individual parts
     *
     * @param string $uri url that will be parsed into array with individual parts
     *
     * @return Array Individual parts of uri in array
     */
    protected function parseUrl($uri)
    {
        $parsedUrl = parse_url($uri);

        return [
            isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : '',
            isset($parsedUrl['host']) ? $parsedUrl['host'] : '',
            isset($parsedUrl['port']) ? $parsedUrl['port'] : null,
            isset($parsedUrl['path']) ? $parsedUrl['path'] : '/',
            isset($parsedUrl['query']) ? $parsedUrl['query'] : '',
            isset($parsedUrl['fragment']) ? $parsedUrl['fragment'] : '',
            isset($parsedUrl['user']) ? $parsedUrl['user'] : '',
            isset($parsedUrl['pass']) ? $parsedUrl['pass'] : ''
        ];
    }
}
