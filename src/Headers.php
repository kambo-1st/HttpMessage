<?php
namespace Kambo\Http\Message;

/**
 * Headers
 *
 * This class represents a collection of HTTP headers that is used in HTTP 
 * request and response objects.
 *
 * @package Kambo\Http\Message
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Headers
{
    /**
     * List of headers name that should not be normalized.
     *
     * @var array
     */
    private $ignoreProcessing = [
        "user-agent" => true
    ];

    /**
     * Header data
     *
     * @var array|null
     */
    private $data;

    /**
     * Constructor
     *
     * @param array|null $headers
     *
     */
    public function __construct(
        $headers = null
    ) {
        if (isset($headers)) {
            $headers = $this->normalizeHeaders($headers);
        }

        $this->data = $headers;
    }

    /**
     * Add header value
     * If the header already exists data are merged, method DOES NOT replace previous value.
     *
     * @param string $name  Name of the header.
     * @param mixed  $value Value of the header can be scalar data type or string.
     *
     * @return void
     */
    public function add($name, $value)
    {
        $name      = $this->normalizeName($name);
        $newValues = is_array($value) ? $value : [$value];
        $data      = [];
        if (isset($this->data[$name])) {
            $data = $this->data[$name];
        }

        $this->data[$name] = array_merge($data, $newValues);
    }

    /**
     * Set header value
     * If the header already exist value will be replaced.
     *
     * @param string $name  Name of the header.
     * @param mixed  $value Value of the header can be scalar data type or string.
     *
     * @return void
     */
    public function set($name, $value)
    {
        $name = $this->normalizeName($name);
        $newValues = is_array($value) ? $value : [$value];
        $this->data[$name] = $newValues;
    }

    /**
     * Get header by provided name
     *
     * @param string $name Name of the header.
     *   
     * @return mixed value of header
     */
    public function get($name)
    {
        $name = $this->normalizeName($name);
        $data = [];
        if (isset($this->data[$name])) {
            $data = $this->data[$name];
        }

        return $data;
    }

    /**
     * Get header values separated by comma
     *
     * @param string $name Name of the header.
     *   
     * @return string value of header separated by comma
     */
    public function getLine($name)
    {
        $line = $this->get($name);

        return implode(',', $line);
    }

    /**
     * Get all headers
     *   
     * @return array value of all headers
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Remove header by provided name
     *
     * @param string $name Name of the header.
     *   
     * @return self
     */
    public function remove($name)
    {
        $name = $this->normalizeName($name);
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }

        return $this;
    }

    /**
     * Check if header exist
     *
     * @param string $name Name of the header.  
     *   
     * @return boolean Returns TRUE if exists or FALSE if not.
     */
    public function exists($name)
    {
        return isset($this->data[$this->normalizeName($name)]);
    }

    // ------------ PRIVATE METHODS

    /**
     * Normalize headers values
     *
     * @param array $headers Headers for normalization.
     *   
     * @return array Normalized header data. 
     */
    private function normalizeHeaders($headers)
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $name              = $this->normalizeName($name);
            $normalized[$name] = $this->normalizeData($name, $value);
        }

        return $normalized;
    }

    /**
     * Normalize header value
     *
     * @param string $name Name of header - some headers should not be normalized.
     * @param string $data Header data for normalization.
     *  
     * @return array Normalized value of header
     */
    private function normalizeData($name, $data)
    {
        if (!isset($this->ignoreProcessing[$name])) {
            return array_map('trim', explode(',', $data));
        } else {
            return [$data];
        }
    }

    /**
     * Normalize header name
     *
     * @param string $name Name of header for normalization
     *  
     * @return string Normalized name of header
     */
    private function normalizeName($name)
    {
        $name = strtr(strtolower($name), '_', '-');
        if (strpos($name, 'http-') === 0) {
            $name = substr($name, 5);
        }

        return $name;
    }
}
