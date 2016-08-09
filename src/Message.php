<?php
namespace Kambo\Http\Message;

// \Spl
use InvalidArgumentException;

// \Http\Message
use Kambo\Http\Message\Stream;
use Kambo\Http\Message\Headers;

// \Psr
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client. This interface defines the methods common to
 * each.
 *
 * Messages are considered immutable; all methods that change state retain the 
 * internal state of the current message and return an instance that contains 
 * the changed state.
 *
 * @link http://www.ietf.org/rfc/rfc7230.txt
 * @link http://www.ietf.org/rfc/rfc7231.txt
 *
 * @package Kambo\Http\Message
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Message implements MessageInterface
{
    /**
     * Protocol version
     *
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * Headers
     *
     * @var Headers
     */
    protected $headers;

    /**
     * Body data
     *
     * @var StreamInterface
     */
    protected $body;

    /**
     * Create a new message
     *
     * @param Headers|array               $headers  The request headers collection
     * @param StreamInterface|string|null $body     The request body object
     * @param string                      $protocol The request version of the protocol
     *
     * @throws \InvalidArgumentException If an unsupported argument type is provided for the body.
     */
    public function __construct(
        $headers = [],
        $body = null,
        $protocol = '1.1'
    ) {
        $this->headers         = $this->normalizeHeaders($headers);
        $this->body            = $this->normalizeBody($body);
        $this->protocolVersion = $protocol;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method retains the state of the current instance, and return
     * an instance that contains the new protocol version.
     *
     * @param string $version HTTP protocol version
     *
     * @return self
     */
    public function withProtocolVersion($version)
    {
        $this->validateProtocol($version);

        $clone                  = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array Returns an associative array of the message's headers. Each
     *               key is a header name, and each value is an array of strings
     *               for that header.
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header name using
     *              a case-insensitive string comparison. Returns false if no matching
     *              header name is found in the message.
     */
    public function hasHeader($name)
    {
        return $this->headers->exists($name);
    }

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
     *                  If the header does not appear in the message, an empty
     *                  array is returned.
     */
    public function getHeader($name)
    {
        return $this->headers->get($name);
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values are appropriately represented using comma concatenation.
     *       If you want values without concatenation, use getHeader() instead and supply
     *       your own delimiter when concatenating.
     *
     * If the header does not appear in the message, an empty string is returned.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header concatenated
     *                together using a comma. If the header does not appear in the message,
     *                this method return an empty string.
     */
    public function getHeaderLine($name)
    {
        return $this->headers->getLine($name);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method retains the state of the current instance, and return
     * an instance with new and/or updated header and value.
     *     
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     *
     * @return self
     *
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        $this->validateHeaderName($name);

        $clone          = clone $this;
        $clone->headers = clone $this->headers;
        $clone->headers->set($name, $value);

        return $clone;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method retains the state of the current instance, and return
     * an instance with new and/or updated header and value.
     *
     * @param string          $name  Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     *
     * @return self
     *
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        $this->validateHeaderName($name);

        $clone          = clone $this;
        $clone->headers = clone $this->headers;
        $clone->headers->add($name, $value);

        return $clone;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution is done without case-sensitivity.
     *
     * This method retains the state of the current instance, and return
     * an instance that removes the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return self
     */
    public function withoutHeader($name)
    {
        $clone          = clone $this;
        $clone->headers = clone $this->headers;
        $clone->headers->remove($name);

        return $clone;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * This method retains the state of the current instance, and return
     * an instance that has the new body stream.
     *     
     * @param StreamInterface $body Body.
     *
     * @return self
     *
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $clone       = clone $this;
        $clone->body = $body;

        return $clone;
    }

    // ------------ PROTECTED METHODS

    /**
     * Provide message headers
     *
     * @return Headers Message headers
     */
    protected function provideHeaders()
    {
        return $this->headers;
    }

    // ------------ PRIVATE METHODS

    /**
     * Normalize provided body and ensure that the result object is Stream.
     *
     * @param StreamInterface|string|null $body The request body object
     *
     * @return Stream Normalized body
     *
     * @throws \InvalidArgumentException If an unsupported argument type is provided.
     */
    private function normalizeBody($body = null)
    {
        $body = $body ? $body : new Stream(fopen('php://temp', 'r+'));
        if (is_string($body)) {
            $memoryStream = fopen('php://temp', 'r+');
            fwrite($memoryStream, $body);
            rewind($memoryStream);
            $body = new Stream($memoryStream);
        } elseif (!($body instanceof StreamInterface)) {
            throw new InvalidArgumentException(
                'Body must be a string, null or implement Psr\Http\Message\StreamInterface'
            );
        }

        return $body;
    }

    /**
     * Normalize provided headers and ensure that the result object is Headers.
     *
     * @param Headers|array $headers The request body object
     *
     * @return Headers Normalized headers
     *
     * @throws \InvalidArgumentException If an unsupported argument type is provided.
     */
    private function normalizeHeaders($headers)
    {
        if (is_array($headers)) {
            $headers = new Headers($headers);
        } elseif (!($headers instanceof Headers)) {
            throw new InvalidArgumentException(
                'Headers must be an array or instance of Headers'
            );
        }

        return $headers;
    }

    /**
     * Validate name of the header - it must not be array.
     *
     * @param string $headerName Name of the header.
     *
     * @throws \InvalidArgumentException When the header name is not valid.
     */
    private function validateHeaderName($headerName)
    {
        if (is_array($headerName)) {
            throw new InvalidArgumentException('Invalid HTTP header name');
        }
    }

    /**
     * Validate version of the HTTP protocol.
     *
     * @param string $version Version of the HTTP protocol.
     *
     * @throws \InvalidArgumentException When the protocol version is not valid.
     */
    private function validateProtocol($version)
    {
        $valid = [
            '1.0' => true,
            '1.1' => true,
            '2.0' => true,
        ];

        if (!isset($valid[$version])) {
            throw new InvalidArgumentException('Invalid HTTP version. Must be one of: 1.0, 1.1, 2.0');
        }
    }
}
