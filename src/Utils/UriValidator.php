<?php
namespace Kambo\Http\Message\Utils;

// \Spl
use InvalidArgumentException;

/**
 * Validate selected parts of uri.
 *
 * @package Kambo\Http\Message\Utils
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriValidator
{
    /**
     * Validate Uri port.
     * Value can be null or integer between 1 and 65535.
     *
     * @param  null|int $port The Uri port number.
     *
     * @return null|int
     *
     * @throws InvalidArgumentException If the port is invalid.
     */
    public function validatePort($port)
    {
        if (!is_null($port) && (!is_integer($port) || ($port <= 1 || $port >= 65535))) {
            throw new InvalidArgumentException(
                'Uri port must be null or an integer between 1 and 65535 (inclusive)'
            );
        }
    }

    /**
     * Validate Uri path.
     *
     * Path must NOT contain query string or URI fragment. It can be object,
     * but then the class must implement __toString method.
     *
     * @param  string|object $path The Uri path
     *
     * @return void
     *
     * @throws InvalidArgumentException If the path is invalid.
     */
    public function validatePath($path)
    {
        // Part of validation is same as for query validation
        $this->validateQuery($path);

        if (strpos($path, '?') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );
        }
    }

    /**
     * Validate query.
     *
     * Path must NOT contain URI fragment. It can be object,
     * but then the class must implement __toString method.
     *
     * @param  string|object $query The query path
     *
     * @return void
     *
     * @throws InvalidArgumentException If the query is invalid.
     */
    public function validateQuery($query)
    {
        if (!is_string($query) && !method_exists($query, '__toString')) {
            throw new InvalidArgumentException(
                'Query must be a string'
            );
        }

        if (strpos($query, '#') !== false) {
            throw new InvalidArgumentException(
                'Query must not contain a URI fragment'
            );
        }
    }
}
