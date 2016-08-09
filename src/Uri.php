<?php
namespace Kambo\Http\Message;

// \Spl
use InvalidArgumentException;

// \Psr
use Psr\Http\Message\UriInterface;

// \Http\Message
use Kambo\Http\Message\Utils\UriValidator;

/**
 * Value object representing a URI.
 *
 * This class represent URIs according to RFC 3986 and to
 * provide methods for most common operations. Additional functionality for
 * working with URIs can be provided on top of the object or externally.
 * Its primary use is for HTTP requests, but may also be used in other
 * contexts.
 *
 * Instances of the uri are considered immutable; all methods that
 * change state retain the internal state of the current message and return
 * an instance that contains the changed state.
 *
 * @link http://tools.ietf.org/html/rfc3986 (the URI specification)
 *
 * @package Kambo\Http\Message
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Uri implements UriInterface
{
    const HTTP  = 'http';
    const HTTPS = 'https';

    /**
     * Http port
     *
     * @var integer
     */
    const HTTP_PORT = 80;

    /**
     * Https port
     *
     * @var integer
     */
    const HTTPS_PORT = 443;

    /**
     * Valid URI schemas
     * Schema can be http, https or empty.
     *
     * @var array in format [ string "schema"=> boolean true (true valid), ... ]
     */
    private $validSchema = [
        '' => true,
        self::HTTPS => true,
        self::HTTP => true,
    ];

    /**
     * URL scheme of the URI.
     * Default value is an empty string.
     *
     * @var string
     */
    private $scheme = '';

    /**
     * Path component of the URI.
     * Default value is an empty string.
     *
     * @var string
     */
    private $path = '';

    /**
     * Query string of the URI.
     * Default value is an empty string.
     *
     * @var string
     */
    private $query = '';

    /**
     * Fragment component of the URI.
     * Default value is an empty string.
     *
     * @var string
     */
    private $fragment = '';

    /**
     * User part of the URI.
     * Default value is an empty string.
     *
     * @var string
     */
    private $user = '';

    /**
     * Password part of the URI.
     * Default value is an empty string.
     *
     * @var string
     */
    private $password = '';

    /**
     * Host component of the URI eg. foo.bar
     *
     * @var string
     */
    private $host;

    /**
     * Port component of the URI eg. 443.
     *
     * @var integer
     */
    private $port;

    /**
     * Instance of uri validator
     *
     * @var UriValidator
     */
    private $validator;

    /**
     * Create new Uri.
     *
     * @param string $scheme   Uri scheme.
     * @param string $host     Uri host.
     * @param int    $port     Uri port number.
     * @param string $path     Uri path.
     * @param string $query    Uri query string.
     * @param string $fragment Uri fragment.
     * @param string $user     Uri user.
     * @param string $password Uri password.
     */
    public function __construct(
        $scheme,
        $host,
        $port = null,
        $path = '/',
        $query = '',
        $fragment = '',
        $user = '',
        $password = ''
    ) {
        $this->scheme   = $this->normalizeScheme($scheme);
        $this->host     = strtolower($host);
        $this->port     = $port;
        $this->path     = $this->normalizePath($path);
        $this->query    = $this->urlEncode($query);
        $this->fragment = $fragment;
        $this->user     = $user;
        $this->password = $password;

        $this->validator = new UriValidator();
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method return an empty string.
     *
     * Underline implementation normalize schema to lowercase, as described in RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and is not added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     *
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method returns an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it is not included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     *
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $port     = $this->getPort();
        $userInfo = $this->getUserInfo();

        return ($userInfo ? $userInfo . '@' : '') . $this->getHost() . ($port ? ':' . $port : '');
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method returns an empty string.
     *
     * If a user is present in the URI, this method return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and is not added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        $userInfo = '';
        if (!empty($this->user)) {
            $userInfo = $this->user;
            if (!empty($this->password)) {
                $userInfo .= ':' . $this->password;
            }
        }

        return $userInfo;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method returns an empty string.
     *
     * Underline implementation normalize the host name to lowercase, as described in RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     *
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method return it as an integer. If the port is the standard port
     * used with the current scheme, this method return null.
     *
     * If no port is present, and no scheme is present, this method returns
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return $this->isStandardPort() ? null : $this->port;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementation support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method does not automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned is percent-encoded, and underline implementation ensure that 
     * the value is not double-encode. Encoded characters are defined in 
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value is passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     *
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method returns an empty string.
     *
     * The leading "?" character is not part of the query and it is not added.
     *
     * The value returned is percent-encoded, and implementation ensure that 
     * the value is not double-encode. Encoded characters are defined in 
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value is passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     *
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query;
    }


    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method returns an empty string.
     *
     * The leading "#" character is not part of the fragment and it is not
     * added.
     *
     * The value returned is percent-encoded, and underline implementation ensure that 
     * the value is not double-encode. Encoded characters are defined in 
     * RFC 3986, Sections 2 and 3.4.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     *
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method retains the state of the current instance, and returns
     * an instance that contains the specified scheme.
     *
     * Implementations support the schemes "http" and "https" case
     * insensitively.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     *
     * @return self A new instance with the specified scheme.
     *
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        $clone         = clone $this;
        $clone->scheme = $this->normalizeScheme($scheme);

        return $clone;
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method retains the state of the current instance, and returns
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string      $user     The user name to use for authority.
     * @param null|string $password The password associated with $user.
     *
     * @return self A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $clone           = clone $this;
        $clone->user     = $user;
        $clone->password = $password;

        return $clone;
    }

    /**
     * Return an instance with the specified host.
     *
     * This method retains the state of the current instance, and returns
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     *
     * @return self A new instance with the specified host.
     *
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        $clone       = clone $this;
        $clone->host = strtolower($host);

        return $clone;
    }

    /**
     * Return an instance with the specified port.
     *
     * This method retains the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Method raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *                       removes the port information.
     *
     * @return self A new instance with the specified port.
     *
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        $this->validator->validatePort($port);

        $clone       = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * Return an instance with the specified path.
     *
     * This method retains the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations support all
     * three syntaxes.
     *
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     *
     * @return self A new instance with the specified path.
     *
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        $this->validator->validatePath($path);

        $clone       = clone $this;
        $clone->path = $this->urlEncode((string) $path);

        return $clone;
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method retains the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementation ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     *
     * @return self A new instance with the specified query string.
     *
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        $this->validator->validateQuery($query);

        $clone        = clone $this;
        $clone->query = $this->urlEncode((string) $query);

        return $clone;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method retains the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementation ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     *
     * @return self A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        $clone           = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it is suffixed by ":".
     * - If an authority is present, it is prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path is
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes are be reduced to one.
     * - If a query is present, it is prefixed by "?".
     * - If a fragment is present, it is prefixed by "#".
     *
     *         foo://example.com:8042/over/there?name=ferret#nose
     *         \_/   \______________/\_________/ \_________/ \__/
     *          |           |            |            |        |
     *       scheme     authority       path        query   fragment   
     *  
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     *
     * @return string
     */
    public function __toString()
    {
        $scheme    = $this->getScheme();
        $authority = $this->getAuthority();
        $path      = $this->getPath();
        $query     = $this->getQuery();
        $fragment  = $this->getFragment();

        $path = '/' . ltrim($path, '/');

        return ($scheme ? $scheme . ':' : '') . ($authority ? '//' . $authority : '')
            . $path . ($query ? '?' . $query : '') . ($fragment ? '#' . $fragment : '');
    }

    // ------------ PRIVATE METHODS

    /**
     * Check if Uri use a standard port.
     *
     * @return bool
     */
    private function isStandardPort()
    {
        return ($this->scheme === self::HTTP && $this->port === self::HTTP_PORT)
            || ($this->scheme === self::HTTPS && $this->port === self::HTTPS_PORT);
    }

    /**
     * Normalize scheme part of Uri.
     *
     * @param string $scheme Raw Uri scheme.
     *
     * @return string Normalized Uri
     *
     * @throws InvalidArgumentException If the Uri scheme is not a string.
     * @throws InvalidArgumentException If Uri scheme is not "", "https", or "http".
     */
    private function normalizeScheme($scheme)
    {
        if (!is_string($scheme) && !method_exists($scheme, '__toString')) {
            throw new InvalidArgumentException('Uri scheme must be a string');
        }

        $scheme = str_replace('://', '', strtolower((string) $scheme));
        if (!isset($this->validSchema[$scheme])) {
            throw new InvalidArgumentException('Uri scheme must be one of: "", "https", "http"');
        }

        return $scheme;
    }

    /**
     * Normalize path part of Uri and ensure it is properly encoded..
     *
     * @param string $path Raw Uri path.
     *
     * @return string Normalized Uri path
     *
     * @throws InvalidArgumentException If the Uri scheme is not a string.
     */
    private function normalizePath($path)
    {
        if (!is_string($path) && !method_exists($path, '__toString')) {
            throw new InvalidArgumentException('Uri path must be a string');
        }

        $path = $this->urlEncode($path);

        if (empty($path)) {
            return '';
        }

        if ($path[0] !== '/') {
            return $path;
        }

        // Ensure only one leading slash
        return '/' . ltrim($path, '/');
    }

    /**
     * Url encode 
     *
     * This method percent-encodes all reserved
     * characters in the provided path string. This method
     * will NOT double-encode characters that are already
     * percent-encoded.
     *
     * @param  string $path The raw uri path.
     *
     * @return string The RFC 3986 percent-encoded uri path.
     *
     * @link   http://www.faqs.org/rfcs/rfc3986.html
     */
    private function urlEncode($path)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }
}
