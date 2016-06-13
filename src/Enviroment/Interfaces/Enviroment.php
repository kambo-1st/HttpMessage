<?php
namespace Kambo\HttpMessage\Enviroment\Interfaces;

/**
 * Enviroment interface
 *
 * @package Kambo\HttpMessage\Enviroment\Interfaces
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
interface Enviroment
{
    /**
     * Get query string
     *
     * @return string query string
     */
    public function getQueryString();

    /**
     * Get request method
     *
     * @return string 
     */
    public function getRequestMethod();

    /**
     * Get request uri
     *
     * @return string
     */
    public function getRequestUri();

    /**
     * Get request scheme
     *
     * @return string
     */
    public function getRequestScheme();

    /**
     * Get host
     *
     * @return string
     */
    public function getHost();

    /**
     * Get port
     *
     * @return int
     */
    public function getPort();

    /**
     * Get body
     *
     * @return string
     */
    public function getBody();

    /**
     * Get cookies
     *
     * @return array
     */
    public function getCookies();

    /**
     * Get files
     *
     * @return array
     */
    public function getFiles();

    /**
     * Get server
     *
     * @return array
     */
    public function getServer();

    /**
     * Get protocol version
     *
     * @return string
     */
    public function getProtocolVersion();

    /**
     * Get auth user
     *
     * @return string
     */
    public function getAuthUser();

    /**
     * Get auth password
     *
     * @return string
     */
    public function getAuthPassword();
}
