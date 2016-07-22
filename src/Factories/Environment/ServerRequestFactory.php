<?php
namespace Kambo\HttpMessage\Factories\Environment;

// \Psr
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

// \HttpMessage
use Kambo\HttpMessage\Uri;
use Kambo\HttpMessage\UploadedFile;
use Kambo\HttpMessage\Headers;
use Kambo\HttpMessage\ServerRequest;
use Kambo\HttpMessage\Stream;

// \HttpMessage\Environment
use Kambo\HttpMessage\Environment\Environment;

// \HttpMessage\Factories
use Kambo\HttpMessage\Factories\Environment\Interfaces\Factory;
use Kambo\HttpMessage\Factories\Environment\Superglobal\FilesFactory;
use Kambo\HttpMessage\Factories\Environment\Superglobal\HeadersFactory;
use Kambo\HttpMessage\Factories\Environment\Superglobal\UriFactory;

/**
 * Create instance of ServerRequest object from instance of Environment object
 *
 * @package Kambo\HttpMessage\Factories\Environment
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ServerRequestFactory implements Factory
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

        return new ServerRequest(
            $requestMethod,
            $uri,
            $bodyStream,
            $headers,
            $serverVariables,
            $cookies,
            $uploadFiles,
            $protocol
        );
    }
}
