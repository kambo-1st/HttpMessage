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
     * @return string query string
     */
    public function getQueryString()
    {
        return $this->enviromentData['QUERY_STRING'];
    }

    /**
     * Get request method
     *
     * @return string 
     */
    public function getRequestMethod()
    {
        return $this->enviromentData['REQUEST_METHOD'];
    }

    /**
     * Get request uri
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->enviromentData['REQUEST_URI'];
    }
    /**
     * Get request scheme
     *
     * @return string
     */
    public function getRequestScheme()
    {
        return $this->enviromentData['REQUEST_SCHEME'];
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->enviromentData['HTTP_HOST'];
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->enviromentData['SERVER_PORT'];
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

    /**
     * Get protocol version
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        $protocol = $this->enviromentData['SERVER_PROTOCOL'];
        list(,$version) = explode("/", $protocol);

        return $version;
    }

    /**
     * Get auth user
     *
     * @return string
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
     * @return string
     */
    public function getAuthPassword()
    {
        if (isset($this->enviromentData['PHP_AUTH_PW'])) {
                return $this->enviromentData['PHP_AUTH_PW'];
        }

        return null;
    }
}
