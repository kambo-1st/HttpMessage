<?php
namespace Kambo\HttpMessage\Factories\Enviroment;

// \Psr
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

// \HttpMessage
use Kambo\HttpMessage\Uri;
use Kambo\HttpMessage\UploadedFile;
use Kambo\HttpMessage\Headers;
use Kambo\HttpMessage\ServerRequest;
use Kambo\HttpMessage\Stream;

// \HttpMessage\Enviroment
use Kambo\HttpMessage\Enviroment\Enviroment;

// \HttpMessage\Factories
use Kambo\HttpMessage\Factories\Enviroment\Interfaces\Factory;
use Kambo\HttpMessage\Factories\Enviroment\Superglobal\FilesFactory;
use Kambo\HttpMessage\Factories\Enviroment\Superglobal\HeadersFactory;
use Kambo\HttpMessage\Factories\Enviroment\Superglobal\UriFactory;

/**
 * Create instance of ServerRequest object from instance of Enviroment object
 *
 * @package Kambo\HttpMessage\Factories\Enviroment
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ServerRequestFactory implements Factory
{
    /**
     * Create instance of ServerRequest object from instance of Enviroment object
     *
     * @param Enviroment $enviroment enviroment data
     *
     * @return ServerRequest Instance of ServerRequest object
     */
    public static function fromEnviroment(Enviroment $enviroment)
    {
        $uri         = UriFactory::fromEnviroment($enviroment);
        $uploadFiles = FilesFactory::fromEnviroment($enviroment);
        $headers     = HeadersFactory::fromEnviroment($enviroment);

        $cookies       = $enviroment->getCookies();
        $requestMethod = $enviroment->getRequestMethod();
        $bodyStream    = new Stream($enviroment->getBody());
        $protocol      = $enviroment->getProtocolVersion();

        $serverVariables = $enviroment->getServer();

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
