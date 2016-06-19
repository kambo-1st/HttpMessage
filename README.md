# kambo httpmessage
[![Build Status](https://img.shields.io/travis/kambo-1st/HttpMessage.svg?branch=master&style=flat-square)](https://travis-ci.org/kambo-1st/HttpMessage)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/kambo-1st/HttpMessage.svg?style=flat-square)](https://scrutinizer-ci.com/g/kambo-1st/HttpMessage/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/kambo-1st/HttpMessage.svg?style=flat-square)](https://scrutinizer-ci.com/g/kambo-1st/HttpMessage/)
[![Dependency Status](https://www.versioneye.com/user/projects/5761a83a0a82b20053182cce/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5761a83a0a82b20053182cce)

Just another PHP implementation of PSR-7 - HTTP message interfaces

## Install

Prefered way to install library is with composer:
```sh
composer require kambo/httpmessage
```

## Usage

### Server request
Creation of ServerRequest instance that encapsulates all data as it has arrived to the
application from the CGI and/or PHP environment:

```php
$enviroment    = new Enviroment($_SERVER, fopen('php://input', 'w+'), $_COOKIE, $_FILES);
$serverRequest = ServerRequestFactory::fromEnviroment($enviroment);
```

## License
The MIT License (MIT), https://opensource.org/licenses/MIT