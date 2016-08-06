<?php
namespace Kambo\Http\Message\Factories\Environment;

// \Http\Message
use Kambo\Http\Message\ServerRequest;
use Kambo\Http\Message\Stream;

// \Http\Message\Environment
use Kambo\Http\Message\Environment\Environment;

// \Http\Message\Factories
use Kambo\Http\Message\Factories\Environment\Interfaces\FactoryInterface;
use Kambo\Http\Message\Factories\Environment\Superglobal\FilesFactory;
use Kambo\Http\Message\Factories\Environment\Superglobal\HeadersFactory;
use Kambo\Http\Message\Factories\Environment\Superglobal\UriFactory;

/**
 * Create instance of ServerRequest object from instance of Environment object
 *
 * @package Kambo\Http\Message\Factories\Environment
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ServerRequestFactory implements FactoryInterface
{
    /**
     * Create instance of ServerRequest object from instance of Environment object
     *
     * @param Environment $environment environment data
     *
     * @return ServerRequest Instance of ServerRequest object
     */
    public function create(Environment $environment)
    {
        $uri         = (new UriFactory())->create($environment);
        $uploadFiles = (new FilesFactory())->create($environment);
        $headers     = (new HeadersFactory())->create($environment);

        $cookies       = $environment->getCookies();
        $requestMethod = $environment->getRequestMethod();
        $bodyStream    = new Stream($environment->getBody());
        $protocol      = $environment->getProtocolVersion();

        $serverVariables = $environment->getServer();

        $serverRequest = new ServerRequest(
            $requestMethod,
            $uri,
            $bodyStream,
            $headers,
            $serverVariables,
            $cookies,
            $uploadFiles,
            $protocol
        );

        // php://input is not available with enctype="multipart/form-data".
        if ($this->usePostAsParsed($requestMethod, $serverRequest)) {
            $serverRequest = $serverRequest->withParsedBody($environment->getPost());
        }

        return $serverRequest;
    }

    /**
     * Check if the body request should be taken from the $_POST super global
     * php://input is not available with enctype="multipart/form-data". POST
     * data are also used if the content type is "application/x-www-form-urlencoded"
     * for performance reason.
     *
     * @param string        $requestMethod Request method GET, POST etc.
     * @param ServerRequest $serverRequest Instance of server request
     *
     * @return Boolean True if the post data should be used as a parsed body
     */
    private function usePostAsParsed($requestMethod, ServerRequest $serverRequest)
    {
        $contentType = '';
        if ($serverRequest->hasHeader('Content-Type')) {
            $contentType = $serverRequest->getHeader('Content-Type')[0];
        }

        return ($requestMethod === 'POST'
            && in_array($contentType, ['application/x-www-form-urlencoded', 'multipart/form-data']));
    }
}
