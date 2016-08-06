<?php
namespace Kambo\Http\Message;

// \Spl
use InvalidArgumentException;

// \Psr
use Psr\Http\Message\UriInterface;

/**
 * Shared methods for outgoing, client-side request and server request.
 *
 * @package Kambo\Http\Message
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
trait RequestTrait
{
    /**
     * Message request target
     *
     * @var string
     */
    private $requestTarget = null;

    /**
     * Method of incoming request - GET, POST, DELETE, PUT, PATCH, HEAD or OPTIONS.
     *
     * @var string
     */
    protected $requestMethod;

    /**
     * Uri of incoming request
     *
     * @var Psr\Http\Message\UriInterface;
     */
    protected $uri;

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header name using
     *              a case-insensitive string comparison. Returns false if no matching
     *              header name is found in the message.
     */
    abstract public function hasHeader($name);

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given header.
     *                  If the header does not appear in the message, this method MUST
     *                  return an empty array.
     */
    abstract public function getHeader($name);

    /**
     * Provide message headers
     *
     * @return Headers Message headers
     */
    abstract protected function provideHeaders();

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget === null) {
            $target = '/';
            if ($this->uri->getPath() !== null) {
                $target = $this->uri->getPath();
                $target .= (!empty($this->uri->getQuery())) ? '?' . $this->uri->getQuery() : '';
            }

            $this->requestTarget = $target;
        }

        return $this->requestTarget;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method retains the state of the current instance, and return
     * an instance that has the changed request target.
     *     
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *       request-target forms allowed in request messages)
     *
     * @param mixed $requestTarget
     *
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        $clone                = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations not
     * modify the given string.
     *
     * This method retains the state of the current instance, and return
     * an instance that changed request method.
     *
     * @param string $method Case-sensitive method.
     *
     * @return self
     *
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        $this->validateMethod($method);

        $clone                = clone $this;
        $clone->requestMethod = $method;

        return $clone;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @return UriInterface Returns a UriInterface instance representing the URI of the request.                
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header is carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the the Host header is missing or empty, and the new URI contains
     *   a host component, this method update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method not update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method not update
     *   the Host header in the returned request.
     *
     * This method retains the state of the current instance, and return
     * an instance that contains the new UriInterface instance.
     *     
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param UriInterface $uri          New request URI to use.
     * @param bool         $preserveHost Preserve the original state of the Host header.
     *
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone      = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            if ($uri->getHost() !== '') {
                $clone->provideHeaders()->set('Host', $uri->getHost());
            }
        } else {
            if ($this->shouldSetHost()) {
                $clone->provideHeaders()->set('Host', $uri->getHost());
            }
        }

        return $clone;
    }

    /**
     * Check if the host header should be set.
     *
     * @return bool true if should be
     */
    protected function shouldSetHost()
    {
        return $this->uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeader('Host') === null);
    }

    /**
     * Validate request method.
     *
     * @param string $method Case-sensitive request method.
     *
     * @return void
     *
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    protected function validateMethod($method)
    {
        $valid = [
            'GET'     => true,
            'POST'    => true,
            'DELETE'  => true,
            'PUT'     => true,
            'PATCH'   => true,
            'HEAD'    => true,
            'OPTIONS' => true,
        ];

        if (!isset($valid[$method])) {
            throw new InvalidArgumentException(
                'Invalid method version. Must be one of: GET, POST, DELETE, PUT, PATCH, HEAD or OPTIONS'
            );
        }
    }
}
