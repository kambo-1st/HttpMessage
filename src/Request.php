<?php
namespace Kambo\Http\Message;

// \Spl
use InvalidArgumentException;

// \Psr
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

// \Http\Message
use Kambo\Http\Message\Message;
use Kambo\Http\Message\Headers;
use Kambo\Http\Message\RequestTrait;
use Kambo\Http\Message\Factories\String\UriFactory;
use Kambo\Http\Message\Stream;

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
 * During construction, implementations attempt to set the Host header from
 * a provided URI if no Host header is provided.
 *
 * Requests are considered immutable; all methods that change state retain
 * the internal state of the current message and return an instance that
 * contains the changed state.
 *
 * @package Kambo\Http\Message
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Request extends Message implements RequestInterface
{
    use RequestTrait;

    /**
     * Create new outgoing HTTP request.
     *
     * Adds a host header when none was provided and a host is defined in URI.
     *
     * @param string                      $requestMethod The request method
     * @param UriInterface|string         $uri           The request URI object
     * @param Headers                     $headers       The request headers collection
     * @param StreamInterface|string|null $body          The request body object
     * @param string                      $protocol      The request version of the protocol
     *
     * @throws \InvalidArgumentException If an unsupported argument type is
     *                                   provided for URI or body.
     */
    public function __construct(
        $requestMethod,
        $uri,
        $headers = [],
        $body = null,
        $protocol = '1.1'
    ) {
        parent::__construct($headers, $body, $protocol);

        if (is_string($uri)) {
            $this->uri = (new UriFactory())->create($uri);
        } elseif ($uri instanceof UriInterface) {
            $this->uri = $uri;
        } else {
            throw new InvalidArgumentException(
                'URI must be a string or implement Psr\Http\Message\UriInterface'
            );
        }

        $this->validateMethod($requestMethod);
        $this->requestMethod = $requestMethod;

        if ($this->shouldSetHost()) {
            $this->headers->set('Host', $this->uri->getHost());
        }
    }
}
