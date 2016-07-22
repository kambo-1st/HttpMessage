<?php
namespace Kambo\HttpMessage\Enviroment;

// \Spl
use InvalidArgumentException;

// \HttpMessage
use Kambo\HttpMessage\Enviroment\Interfaces\Enviroment as EnviromentInterface;

/**
 * Contains information about server and HTTP request - headers, cookies, files and body data.
 *
 * @package Kambo\HttpMessage\Enviroment\Interfaces\Enviroment
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Enviroment implements EnviromentInterface
{
    /**
     * Enviroment data, must have same structure as $_SERVER superglobal.
     *
     * @var array
     */
    private $enviromentData;

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
     * Constructor
     *
     * @param array    $server An associative array containing information such as headers, paths, 
     *                         and script locations. Must have same structure as $_SERVER.
     * @param resource $body   Raw data from the request body.
     * @param array    $cookie An associative array constructed from cookies, must have same structure as $_COOKIE. 
     * @param array    $files  An associative array of uploaded items, must have same structure as $_FILES.
     *
     */
    public function __construct(array $server, $body, $cookie = [], $files = [])
    {
        if (!is_resource($body)) {
            throw new InvalidArgumentException('Provided body must be of type resource.');
        }

        $this->enviromentData = $server;
        $this->body           = $body;
        $this->cookies        = $cookie;
        $this->files          = $files;
    }

    /**
     * Get query string
     *
     * @return string|null query string
     */
    public function getQueryString()
    {
        return isset($this->enviromentData['QUERY_STRING']) ? $this->enviromentData['QUERY_STRING'] : null;
    }

    /**
     * Get request method
     *
     * @return string|null
     */
    public function getRequestMethod()
    {
        return isset($this->enviromentData['REQUEST_METHOD']) ? $this->enviromentData['REQUEST_METHOD'] : null;
    }

    /**
     * Get request uri
     *
     * @return string|null
     */
    public function getRequestUri()
    {
        return isset($this->enviromentData['REQUEST_URI']) ? $this->enviromentData['REQUEST_URI'] : null;
    }
    /**
     * Get request scheme
     *
     * @return string|null
     */
    public function getRequestScheme()
    {
        return isset($this->enviromentData['REQUEST_SCHEME']) ? $this->enviromentData['REQUEST_SCHEME'] : null;
    }

    /**
     * Get host
     *
     * @return string|null
     */
    public function getHost()
    {
        return isset($this->enviromentData['HTTP_HOST']) ? $this->enviromentData['HTTP_HOST'] : null;
    }

    /**
     * Get port
     *
     * @return int|null
     */
    public function getPort()
    {
        return isset($this->enviromentData['SERVER_PORT']) ? $this->enviromentData['SERVER_PORT'] : null;
    }

    /**
     * Get protocol version
     *
     * @return string|null
     */
    public function getProtocolVersion()
    {
        $version = null;
        if (isset($this->enviromentData['SERVER_PROTOCOL'])) {
            $protocol = $this->enviromentData['SERVER_PROTOCOL'];
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
        if (isset($this->enviromentData['PHP_AUTH_USER'])) {
            return $this->enviromentData['PHP_AUTH_USER'];
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
        if (isset($this->enviromentData['PHP_AUTH_PW'])) {
            return $this->enviromentData['PHP_AUTH_PW'];
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
        return $this->enviromentData;
    }
}
