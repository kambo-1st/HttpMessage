<?php
namespace Kambo\Http\Message\Factories\Environment;

// \Psr
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

// \Http\Message
use Kambo\Http\Message\Uri;
use Kambo\Http\Message\UploadedFile;
use Kambo\Http\Message\Headers;
use Kambo\Http\Message\ServerRequest;
use Kambo\Http\Message\Stream;

// \Http\Message\Environment
use Kambo\Http\Message\Environment\Environment;

// \Http\Message\Factories
use Kambo\Http\Message\Factories\Environment\Interfaces\Factory;
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
