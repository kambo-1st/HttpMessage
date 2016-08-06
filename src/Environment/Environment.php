<?php
namespace Kambo\Http\Message\Environment;

// \Spl
use InvalidArgumentException;

// \Http\Message
use Kambo\Http\Message\Environment\Interfaces\EnvironmentInterface;

/**
 * Contains information about server and HTTP request - headers, cookies, files and body data.
 *
 * @package Kambo\Http\Message\Environment\Interfaces\Environment
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Environment implements EnvironmentInterface
{
    /**
     * Server environment data, must have same structure as $_SERVER superglobal.
     *
     * @var array
     */
    private $server;

    /**
     * Raw data from the request body.
     *
     * @var resource
     */
    private $body;

    /**
     * An associative array constructed from cookies, must have same structure as $_COOKIE.
     *
     * @var array
     */
    private $cookies;

    /**
     * An associative array of uploaded items, must have same structure as $_FILES.
     *
     * @var array
     */
    private $files;

    /**
     * An associative array of variables passed to the current script via the HTTP POST method.
     *
     * @var array
     */
    private $post;

    /**
     * Constructor
     *
     * @param array    $server An associative array containing information such as headers, paths, 
     *                         and script locations. Must have same structure as $_SERVER.
     * @param resource $body   Raw data from the request body.
     * @param array    $post   An associative array of variables passed to the current script via the HTTP POST method.
     * @param array    $cookie An associative array constructed from cookies, must have same structure as $_COOKIE. 
     * @param array    $files  An associative array of uploaded items, must have same structure as $_FILES.
     *
     */
    public function __construct(array $server, $body, $post = [], $cookie = [], $files = [])
    {
        if (!is_resource($body)) {
            throw new InvalidArgumentException('Provided body must be of type resource.');
        }

        $this->server  = $server;
        $this->body    = $body;
        $this->post    = $post;
        $this->cookies = $cookie;
        $this->files   = $files;
    }

    /**
     * Get query string
     *
     * @return string|null query string
     */
    public function getQueryString()
    {
        return isset($this->server['QUERY_STRING']) ? $this->server['QUERY_STRING'] : null;
    }

    /**
     * Get request method
     *
     * @return string|null
     */
    public function getRequestMethod()
    {
        return isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : null;
    }

    /**
     * Get request uri
     *
     * @return string|null
     */
    public function getRequestUri()
    {
        return isset($this->server['REQUEST_URI']) ? $this->server['REQUEST_URI'] : null;
    }
    /**
     * Get request scheme
     *
     * @return string|null
     */
    public function getRequestScheme()
    {
        return isset($this->server['REQUEST_SCHEME']) ? $this->server['REQUEST_SCHEME'] : null;
    }

    /**
     * Get host
     *
     * @return string|null
     */
    public function getHost()
    {
        return isset($this->server['HTTP_HOST']) ? $this->server['HTTP_HOST'] : null;
    }

    /**
     * Get port
     *
     * @return int|null
     */
    public function getPort()
    {
        return isset($this->server['SERVER_PORT']) ? $this->server['SERVER_PORT'] : null;
    }

    /**
     * Get protocol version
     *
     * @return string|null
     */
    public function getProtocolVersion()
    {
        $version = null;
        if (isset($this->server['SERVER_PROTOCOL'])) {
            $protocol = $this->server['SERVER_PROTOCOL'];
            list(,$version) = explode("/", $protocol);
        }

        return $version;
    }

    /**
     * Get auth user
     *
     * @return string|null
     */
    public function getAuthUser()
    {
        if (isset($this->server['PHP_AUTH_USER'])) {
            return $this->server['PHP_AUTH_USER'];
        }

        return null;
    }

    /**
     * Get auth password
     *
     * @return string|null
     */
    public function getAuthPassword()
    {
        if (isset($this->server['PHP_AUTH_PW'])) {
            return $this->server['PHP_AUTH_PW'];
        }

        return null;
    }

    /**
     * Get body
     *
     * @return resource
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get body
     *
     * @return resource
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Get cookies
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Get files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Get server
     *
     * @return array
     */
    public function getServer()
    {
        return $this->server;
    }
}
