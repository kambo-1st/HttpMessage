# kambo httpmessage
[![Build Status](https://img.shields.io/travis/kambo-1st/HttpMessage.svg?branch=master&style=flat-square)](https://travis-ci.org/kambo-1st/HttpMessage)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/kambo-1st/HttpMessage.svg?style=flat-square)](https://scrutinizer-ci.com/g/kambo-1st/HttpMessage/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/kambo-1st/HttpMessage.svg?style=flat-square)](https://scrutinizer-ci.com/g/kambo-1st/HttpMessage/)
[![Dependency Status](https://www.versioneye.com/user/projects/5761a83a0a82b20053182cce/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5761a83a0a82b20053182cce)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Standalone complete implementation of PSR-7 - HTTP message interfaces.

Package is not depended on any existing framework and implements only functionality described in PSR-7. 

## Install

Prefered way to install library is with composer:
```sh
composer require kambo/httpmessage
```

## Basic usage

### Factories
Package comes with the set of factory classes for creating instances of ```Files```, ```Headers```, ```Uri``` and ```ServerRequest``` classes from the super global variables (```$_POST```, ```$_COOKIE```, ```$_FILES``` etc.).
Each of object is created by calling method ```create``` on corresponding factory. Only parameter is instance of environment class. For example following code will create instance of ```ServerRequest```:

```php
// Create Environment object based on server variables.
$environment = new Environment($_SERVER, fopen('php://input', 'w+'), $_POST, $_COOKIE, $_FILES);
// Create instance of ServerRequest object
$serverRequest = (new ServerRequestFactory())->create($environment);
```

Environment object is simple wrapper around server and CGI variables and is usually instanced from the super global variables (```$_POST```, ```$_COOKIE```, ```$_FILES``` etc.).

Constructor of ```Environment``` class has two mandatory parameters - ```server``` and ```body```.

Server is an associative array containing information such as headers, paths, and script locations, it must have same structure as the super global variable ```$_SERVER```.
Body is ```resource```, created from raw data from the request body. Usually it should be created from the ```php://input``` stream.

Constructor also accept three optional parameters - post, cookie and files. Each of these parameters is counterpart to super global variable and
it must have same structure eg.: cookie must have same structure as the ```$_COOKIE``` variable. If some of these parameters is missing an empty array is used.

```php
// Create Environment object based on server variables.
$environment = new Environment($_SERVER, fopen('php://input', 'w+'), $_POST, $_COOKIE, $_FILES); 
```

### Server request - ServerRequest
Server request is representation of an incoming, server-side HTTP request.

Per the HTTP specification, this class includes properties for
each of the following:
- Protocol version
- HTTP method
- URI
- Headers
- Message body

Additionally, it encapsulates all data as it has arrived to the
application from the CGI and/or PHP environment, including:
- The values represented in $_SERVER.
- Any cookies provided (generally via $_COOKIE)
- Query string arguments (generally via $_GET, or as parsed via parse_str())
- Upload files, if any (as represented by $_FILES)
- Deserialized body parameters (generally from $_POST)

Creation of ```ServerRequest``` instance is done through ```ServerRequestFactory``` from existing super global variables ($_POST, $_COOKIE, $_FILES, etc.):

```php
$environment   = new Environment($_SERVER, fopen('php://input', 'w+'), $_POST, $_COOKIE, $_FILES);
$serverRequest = (new ServerRequestFactory())->create($environment);
```
#### Working with ServerRequest
##### Getting values

ServerRequest comes with lot of handy methods for working with the request:

```php
// Create ServerRequest from existing super global variables
$environment   = new Environment($_SERVER, fopen('php://input', 'w+'), $_POST, $_COOKIE, $_FILES);
$serverRequest = (new ServerRequestFactory())->create($environment);

// Return method of server request eg. GET
$serverRequest->getMethod();
// Return URI server request eg. http://foo.bar
$serverRequest->getUri();
// Return URI server query parameters eg. [ 'foo' => 'bar' ]
$serverRequest->getQueryParams();
// Get server parameters ($_SERVER) 
$serverRequest->getServerParams();
// Get request cookies ($_COOKIE)
$serverRequest->getCookieParams();
// Returns an associative array of the message's headers
$serverRequest->getHeaders();
```

One of the most important part of the request its body.  Request body can be obtained by calling method ```getParsedBody```:

```php
// Get parsed body of request
$serverRequest->getParsedBody();
```

Package parse raw body of request according the content type of request. Following content type are supported:

- json, if the content type is application/json, 
- xml, if the content type is application/xml or text/xml (instance of SimpleXMLElement is returned) and
- query string, if the content type is application/x-www-form-urlencoded or multipart/form-data

Uploaded files are stored as tree of UploadedFile class instances. They can be obtained with method ```getUploadedFiles```:

```php
// Get request uploaded files as tree in following format: 
// <name of upload field> => [ <instance of UploadedFile class>, ... ]
$serverRequest->getUploadedFiles();
```


##### Modifying request

Request can be modified by methods that are prefixed by _with_ string. For example:

- withMethod() - change method of the request
- withQueryParams() - change query params
- withParsedBody() - change parsed body
- withCookieParams() - change cookie parameters

As the requests are immutable; all methods that change state retain the internal state of the current message and return an instance that contains the changed state.

```php
$requestWithGet = $serverRequest->withMethod('GET');
$requestWithPost = $requestWithGet->withMethod('POST');

echo $requestWithGet; // print GET
echo $requestWithPost; // print POST
```

### Stream
Stream provides a wrapper around the most common PHP streams operations, including serialization of the entire stream to a string.

Stream is usually used for describing of ```Request``` or ```Response``` body content. 

New instance of Stream must be created from existing ```Resource```:

```php
// Create Stream from based on raw data from the request body.
$stream = new Stream(fopen('php://input', 'w+'));
```

Content of ```Stream``` can be easily converted to string with method ```getContents```:

```php
// Create Stream from based on raw data from the request body:
$stream = new Stream(fopen('php://input', 'w+'));
// Convert Stream into string:
echo $stream->getContents();
```

### Request
Request is representation of an outgoing, client-side request. It can for example represent request to some third party website.

Request object is created with method and URI as the parameters:

```php
// Create request with GET method to the URI 'foo.bar':
$clientRequest = new Request('GET', 'foo.bar');
echo $clientRequest->getMethod(); // GET
```

Request object constructor also accept three additional optional parameters - headers, body and protocol.

Headers are represented by instance of ```Headers``` object, following snippet show creating request with header ```X-Foo``` set to value ```Bar```:

```php
// Prepare array with header X-Foo and value Bar:
$headers = ['X-Foo' => 'Bar'];
// Create request with GET method to the uri 'foo.bar' with header 'X-Foo: Bar':
$clientRequest = new Request('GET', 'foo.bar', $headers); 
```

Request body can be string or instance of ```Stream``` class, if the string is provided an instance of ```Stream``` will be created from this string:

```php
// Create request with GET method to the uri 'foo.bar' with body :
$clientRequest = new Request('GET', 'foo.bar', [], 'string body');
// Body is of type Stream it must be typecast to the string:
echo (string)$clientRequest->getBody(); // string body
```

Request is immutable; all methods that change state retain the internal state of the instance and return a new instance that contains the changed state.

_Note: package do not provide client for performing the actual request._

### Response
Response is representation of an outgoing, server-side response. Usually represents data that will be send to a browser.

Constructor of response object has three optional parameters - status, headers and body. If the status is not provided status code 200 ('OK') is used. Default value for the rest of parameters is null.

```php
// Create response with status 200, empty headers and body.
$response = new Response();
// 
```

If you want to include additional headers you can do it in same way as in ```Request```, by creating and setting instance of ```Headers``` class:

```php
// Prepare array with header X-Foo and value Bar
$headers = ['X-Foo' => 'Bar'];
// Create response with status code 200 and header 'X-Foo: Bar'
$response = new Response(200, $headers); 
```

Treatment of body is also same as in the case of ```Request``` class, it can be an instance of ```Stream``` or string.

```php
// Create response with status code 200, empty headers and 
$response = new Response(200, null, 'string body');
// Body is instance of Stream it must be typecast to the string.
echo (string)$response->getBody(); // string body
```

_Response is immutable; all methods that change state retain the internal state of the instance and return a new instance that contains the changed state._

## License
The MIT License (MIT), https://opensource.org/licenses/MIT
