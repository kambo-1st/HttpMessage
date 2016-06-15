<?php
namespace Kambo\HttpMessage;

// \Spl
use InvalidArgumentException;

// \Psr
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

// \HttpMessage
use Kambo\HttpMessage\Uri;
use Kambo\HttpMessage\Message;
use Kambo\HttpMessage\Headers;
use Kambo\HttpMessage\RequestTrait;
use Kambo\HttpMessage\Factories\String\UriFactory;

/**
 * Representation of an outgoing, client-side request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * During construction, implementations MUST attempt to set the Host header from
 * a provided URI if no Host header is provided.
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 *
 * @package Kambo\HttpMessage
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Request extends Message implements RequestInterface
{
    use RequestTrait;

    /**
     * Create new outgoing HTTP request.
     *
     * Adds a host header when none was provided and a host is defined in uri.
     *
     * @param string                      $requestMethod The request method
     * @param UriInterface|string         $uri           The request URI object
     * @param HeadersInterface            $headers       The request headers collection
     * @param StreamInterface|string|null $body          The request body object
     * @param string                      $protocol      The request version of the protocol
     *
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function __construct(
        $requestMethod,
        $uri,
        $headers = null,
        $body = null,
        $protocol = '1.1'
    ) {
        if (is_string($uri)) {
            $this->uri = UriFactory::create($uri);
        } elseif (!($uri instanceof UriInterface)) {
            throw new InvalidArgumentException(
                'URI must be a string or Psr\Http\Message\UriInterface'
            );
        }

        if (!isset($headers)) {
            $headers = new Headers();
        }

        $this->validateMethod($requestMethod);
        $this->requestMethod   = $requestMethod;
        $this->headers         = $headers;
        $this->body            = $body;
        $this->protocolVersion = $protocol;

        if ($this->uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeader('Host') === null)) {
            $this->headers->set('Host', $this->uri->getHost());
        }
    }
}
