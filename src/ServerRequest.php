<?php
namespace Kambo\Http\Message;

// \Spl
use InvalidArgumentException;

// \Psr
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

// \Http\Message
use Kambo\Http\Message\Uri;
use Kambo\Http\Message\Message;
use Kambo\Http\Message\UploadedFile;
use Kambo\Http\Message\Headers;
use Kambo\Http\Message\Parser\Parser;
use Kambo\Http\Message\RequestTrait;

/**
 * Representation of an incoming, server-side HTTP request.
 *
 * Per the HTTP specification, this class includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * Additionally, it encapsulates all data as it has arrived to the
 * application from the CGI and/or PHP environment, including:
 *
 * - The values represented in $_SERVER.
 * - Any cookies provided (generally via $_COOKIE)
 * - Query string arguments (generally via $_GET, or as parsed via parse_str())
 * - Upload files, if any (as represented by $_FILES)
 * - Deserialized body parameters (generally from $_POST)
 *
 * $_SERVER values are treated as immutable, as they represent application
 * state at the time of request; as such, no methods are provided to allow
 * modification of those values. The other values provide such methods, as they
 * can be restored from $_SERVER or the request body, and may need treatment
 * during the application (e.g., body parameters may be deserialized based on
 * content type).
 *
 * Additionally, this class recognizes the utility of introspecting a
 * request to derive and match additional parameters (e.g., via URI path
 * matching, decrypting cookie values, deserializing non-form-encoded body
 * content, matching authorization headers to users, etc). These parameters
 * are stored in an "attributes" property.
 *
 * Requests are considered immutable; all methods that change state retain
 * the internal state of the current message and return an instance that
 * contains the changed state.
 *
 * @package Kambo\Http\Message
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ServerRequest extends Message implements ServerRequestInterface
{
    use RequestTrait;

    /**
     * Server parameters - related to the incoming request environment, 
     * they are typically derived from PHP's $_SERVER superglobal.
     *
     * @var array
     */
    private $serverVariables;

    /**
     * Deserialized query string arguments, if any.
     *
     * @var array
     */
    private $queryParams = null;

    /**
     * Cookies sent by the client to the server.
     *
     * @var array
     */
    private $cookies;

    /**
     * Contain attributes derived from the request.
     *
     * @var array
     */
    private $attributes = [];

    /**
     * Uploaded files of incoming request, if any.
     *
     * @var array
     */
    private $uploadedFiles = null;

    /**
     * Parsed incoming request body - this value is filled 
     * when method getParsedBody or withParsedBody is called.
     *
     * @var array|object|null
     */
    private $parsedBody = null;

    /**
     * Create new HTTP request.
     *
     * Adds a host header when none was provided and a host is defined in uri.
     * 
     * @param string          $requestMethod   The request method
     * @param UriInterface    $uri             The request URI object
     * @param StreamInterface $body            The request body object
     * @param Headers|array   $headers         The request headers collection
     * @param array           $serverVariables The server environment variables
     * @param array           $cookies         The request cookies collection
     * @param array           $uploadFiles     The request uploadedFiles collection
     * @param string          $protocol        The request version of the protocol
     * @param array           $attributes      The request attributs
     *
     */
    public function __construct(
        $requestMethod,
        Uri $uri,
        StreamInterface $body,
        $headers,
        array $serverVariables,
        array $cookies,
        array $uploadFiles,
        $protocol,
        array $attributes = []
    ) {
        parent::__construct($headers, $body, $protocol);
        $this->validateMethod($requestMethod);
        $this->uri             = $uri;
        $this->cookies         = $cookies;
        $this->requestMethod   = $requestMethod;
        $this->uploadedFiles   = $uploadFiles;
        $this->attributes      = $attributes;
        $this->serverVariables = $serverVariables;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverVariables;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data are compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookies;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method not update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method retain the immutability of the message, and return an instance 
     * that has the updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     *
     * @return self for fluent interface
     */
    public function withCookieParams(array $cookies)
    {
        $clone          = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        if ($this->queryParams === null) {
            parse_str($this->uri->getQuery(), $this->queryParams);
        }

        return $this->queryParams;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values remain immutable over the course of the incoming
     * request.
     *
     * Setting query string arguments not change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method retain the immutability of the message, and return an instance 
     * that has the updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from $_GET.
     *
     * @return self for fluent interface
     */
    public function withQueryParams(array $query)
    {
        $clone              = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values can be prepared from $_FILES or the message body during
     * instantiation, or can be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *               array is returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method retain the immutability of the message, and return an instance 
     * that has the updated body parameters.
     *
     * @param array An array tree of UploadedFileInterface instances.
     *
     * @return self for fluent interface
     *
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->validateUploadedFiles($uploadedFiles);
        $clone                = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types are arrays or objects. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *                           These will typically be an array or object.
     */
    public function getParsedBody()
    {
        if ($this->body !== null && $this->parsedBody === null) {
            $parser = new Parser($this->getContentType());
            $this->parsedBody = $parser->parse($this->body);
        }

        return $this->parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method retain the immutability of the message, and return an 
     * instance that has the updated body parameters.
     *     
     * @param null|array|object $data The deserialized body data. This will
     *                                typically be in an array or object.
     *
     * @return self for fluent interface
     *
     * @throws \InvalidArgumentException if an unsupported argument type is provided.    
     */
    public function withParsedBody($data)
    {
        if (!is_null($data) && !is_object($data) && !is_array($data)) {
            throw new InvalidArgumentException('Value must be an array, an object, or null');
        }

        $clone             = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and is mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     *
     * @param string $name    The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        $attributeValue = $default;
        if (isset($this->attributes[$name])) {
            $attributeValue = $this->attributes[$name];
        }

        return $attributeValue;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method retains the state of the current instance, and return
     * an instance that has the updated attribute.
     *
     * @see getAttributes()
     *
     * @param string $name  The attribute name.
     * @param mixed  $value The value of the attribute.
     *
     * @return self for fluent interface
     */
    public function withAttribute($name, $value)
    {
        $clone                    = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method retains the state of the current instance, and return
     * an instance that removes the attribute.
     *
     * @see getAttributes()
     *
     * @param string $name The attribute name.
     *
     * @return self for fluent interface
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;

        if (isset($clone->attributes[$name])) {
            unset($clone->attributes[$name]);
        }

        return $clone;
    }

    // ------------ PRIVATE METHODS

    /**
     * Validate request method
     *
     * @param string $method request method
     *
     * @return self for fluent interface
     *
     * @throws \InvalidArgumentException if an unsupported method is provided.     
     */
    private function validateMethod($method)
    {
        $valid = [
            'GET'    => true,
            'POST'   => true,
            'DELETE' => true,
            'PUT'    => true,
            'PATCH'  => true
        ];

        if (!isset($valid[$method])) {
            throw new InvalidArgumentException(
                'Invalid method version. Must be one of: GET, POST, DELTE, PUT or PATCH'
            );
        }
    }

    /**
     * Get request content type.
     *
     * @return string|null The request content type, if known
     */
    private function getContentType()
    {
        $result = null;
        if ($this->hasHeader('Content-Type')) {
            $result = $this->getHeader('Content-Type')[0];
        }

        return $result;
    }

    /**
     * Validate the structure in an uploaded files array.
     *
     * @param array $uploadedFiles
     *
     * @throws InvalidArgumentException if any entry is not an UploadedFileInterface instance.
     */
    private function validateUploadedFiles(array $uploadedFiles)
    {
        foreach ($uploadedFiles as $file) {
            if (is_array($file)) {
                $this->validateUploadedFiles($file);
            } elseif (!$file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid entry in uploaded files structure');
            }
        }
    }
}
