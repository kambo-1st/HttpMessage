<?php
namespace Kambo\Http\Message;

// \Spl
use \InvalidArgumentException;

// \Psr
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

// \Http\Message
use Kambo\Http\Message\Message;
use Kambo\Http\Message\Headers;

/**
 * Representation of an outgoing, server-side response.
 *
 * Per the HTTP specification, this class encapsulate properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * Responses are considered immutable; all methods that change state retain 
 * the internal state of the current message and return an instance that 
 * contains the changed state.
 *
 * @package Kambo\Http\Message
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Response extends Message implements ResponseInterface
{
    /**
     * The response status code.
     *
     * @var int
     */
    private $status = 200;

    /**
     * The response reason phrase associated with the status code.
     *
     * @var string
     */
    private $reasonPhrase = '';

    /**
     * Status codes and reason phrases
     *
     * @var array
     */
    private $messages = [
        //Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        //Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        //Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        //Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        //Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * Create new outgoing, server-side response.
     *
     * @param int                          $status  The response status code.
     * @param Headers|array                $headers The response headers.
     * @param StreamInterface|string|null  $body    The response body.
     */
    public function __construct($status = 200, $headers = [], StreamInterface $body = null)
    {
        parent::__construct($headers, $body);
        $this->validateStatus($status);

        $this->status = $status;
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, the RFC 7231 or IANA recommended reason 
     * phrase is returned according response status code.
     *
     * This method retain the immutability of the message, and return an 
     * instance that has the updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the provided status code;
     *                             if none is provided, implementations use the defaults
     *                             as suggested in the HTTP specification.
     *
     * @return self
     *
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $clone = clone $this;

        $this->validateStatus($code);

        $clone->status = $code;

        if ($reasonPhrase === '') {
            $reasonPhrase = $this->messages[$code];
        }

        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * The default RFC 7231 recommended reason phrase is selected according 
     * response's status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @return string Reason phrase; return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        if ($this->reasonPhrase) {
            return $this->reasonPhrase;
        }

        if (isset($this->messages[$this->status])) {
            return $this->messages[$this->status];
        }

        return '';
    }

    // ------------ PRIVATE METHODS

    /**
     * Validate status code
     *
     * @param int $code The 3-digit integer result code to set.
     *
     * @return void
     * @throws \InvalidArgumentException For invalid status code.
     */
    private function validateStatus($code)
    {
        if (!is_numeric($code)
            || is_float($code)
            || $code < 100
            || $code >= 600
        ) {
            throw new InvalidArgumentException(
                'Status code must be an integer between 100 and 599, inclusive'
            );
        }
    }
}
