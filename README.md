[![Latest Stable Version](https://img.shields.io/packagist/v/loophp/unaltered-psr-http-message-bridge-bundle.svg?style=flat-square)](https://packagist.org/packages/loophp/unaltered-psr-http-message-bridge-bundle)
 [![GitHub stars](https://img.shields.io/github/stars/loophp/unaltered-psr-http-message-bridge-bundle.svg?style=flat-square)](https://packagist.org/packages/loophp/unaltered-psr-http-message-bridge-bundle)
 [![Total Downloads](https://img.shields.io/packagist/dt/loophp/unaltered-psr-http-message-bridge-bundle.svg?style=flat-square)](https://packagist.org/packages/loophp/unaltered-psr-http-message-bridge-bundle)
 [![GitHub Workflow Status](https://img.shields.io/github/workflow/status/loophp/unaltered-psr-http-message-bridge-bundle/Continuous%20Integration/master?style=flat-square)](https://github.com/loophp/unaltered-psr-http-message-bridge-bundle/actions)
 [![Scrutinizer code quality](https://img.shields.io/scrutinizer/quality/g/loophp/unaltered-psr-http-message-bridge-bundle/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/loophp/unaltered-psr-http-message-bridge-bundle/?branch=master)
 [![Type Coverage](https://shepherd.dev/github/loophp/unaltered-psr-http-message-bridge-bundle/coverage.svg)](https://shepherd.dev/github/loophp/unaltered-psr-http-message-bridge-bundle)
 [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/loophp/unaltered-psr-http-message-bridge-bundle/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/loophp/unaltered-psr-http-message-bridge-bundle/?branch=master)
 [![License](https://img.shields.io/packagist/l/loophp/unaltered-psr-http-message-bridge-bundle.svg?style=flat-square)](https://packagist.org/packages/loophp/unaltered-psr-http-message-bridge-bundle)
 [![Donate!](https://img.shields.io/badge/Sponsor-Github-brightgreen.svg?style=flat-square)](https://github.com/sponsors/drupol)

# Unaltered PSR HTTP Message Bridge Bundle

An opt-in and drop-in replacement bundle for [symfony/psr-http-message-bridge](https://github.com/symfony/psr-http-message-bridge)
that doesn't alter the query parameters.

This package provides a PSR Http Message Factory and the Symfony wiring configuration.

The only difference with the package [symfony/psr-http-message-bridge](https://github.com/symfony/psr-http-message-bridge)
is that it doesn't alter the query parameters when converting a Symfony request into a PSR7 request.

Context
* https://3v4l.org/diaBU
* https://github.com/symfony/symfony/issues/29664
* https://externals.io/message/106213
* https://bugs.php.net/bug.php?id=40000
* https://stackoverflow.com/questions/68651/get-php-to-stop-replacing-characters-in-get-or-post-arrays
* https://www.drupal.org/project/drupal/issues/2984272
* https://tracker.moodle.org/browse/MDL-29700
* https://laracasts.com/discuss/channels/laravel/any-way-to-stop-replacing-with

### TL;DR

Symfony's [Request class](https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php)
uses [parse_str()](https://www.php.net/manual/en/function.parse-str.php) function to parse the
query string, but `parse_str()` alter the parameter key if it contains `.` and replaces them with `_`.
This issue makes the Request object harder to work with when we some logic needs to heavily rely on query parameters
([API Platform](https://api-platform.com/), [CAS](https://github.com/ecphp/cas-bundle), ... ).

# Requirements

* PHP >= 7.1.3
* Symfony >= 4

# Installation

```bash
composer require loophp/unaltered-psr-http-message-bridge-bundle
```

The bundle will automatically do the necessary wiring so that when you request a `HttpMessageFactoryInterface`, it will
use the one provided by this bundle.

# Usage

```php
<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

class MainController {
    /**
     * @Route("/api/offers", name="api_offers")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request, HttpMessageFactoryInterface $httpMessageFactory) {
        // Using Symfony's request object.
        $uri = $request->getUri(); // http://localhost:8000/api/offers?product_color=red
        $params = $request->query->all(); // [ 'product_color' => 'red' ]

        // Using PSR Request.
        $psrRequest = $httpMessageFactory->createRequest($request);
        $uri = (string) $psrRequest->getUri(); // http://localhost:8000/api/offers?product.color=red
        $params = $psrRequest->getQueryParams(); // [ 'product.color' => 'red' ]

        return new Response('');
    }
}
```

Notice that the query string parameters has been altered, from `field.filter` to `field_filter` when using the
Symfony Request object.

# Configuration

This package act as a drop-in replacement for [symfony/psr-http-message-bridge](https://github.com/symfony/psr-http-message-bridge)
by decorating the original service.

There is no bundle configuration and you do not have to do anything besides requiring this package in your application.

# Advanced configuration

Depending on what you want to do, you could also add this piece of configuration in your application.

Despite the fact that a request is not a service, you can use it everywhere.

This configuration will provide `@psr.request` into the container that will contain the Symfony request converted
in a PSR-7 message.

```yaml
services:
    symfony.request:
        class: Symfony\Component\HttpFoundation\RequestStack
        factory: [ '@request_stack', getCurrentRequest]

    Psr\Http\Message\RequestInterface:
        factory: ['@sensio_framework_extra.psr7.http_message_factory', 'createRequest']
        arguments: ['@symfony.request']

    psr.request:
        alias: 'Psr\Http\Message\RequestInterface'
```

Thanks to it, you will also be able to get the PSR-7 request in a controller using auto-wiring.

Notice the factory service `@sensio_framework_extra.psr7.http_message_factory` is used, but it will be replaced with
`@loophp\UnalteredPsrHttpMessageBridgeBundle\Factory\UnalteredPsrHttpFactory` internally automatically by Symfony when
installing this package.

```php
<?php

namespace App\Controller;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController {
    /**
     * @Route("/api/offers", name="api_offers")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request, RequestInterface $psrRequest) {
        // Using Symfony's request object.
        $uri = $request->getUri(); // http://localhost:8000/api/offers?product_color=red
        $params = $request->query->all(); // [ 'product_color' => 'red' ]

        // Using PSR Request.
        $uri = (string) $psrRequest->getUri(); // http://localhost:8000/api/offers?product.color=red
        $params = $psrRequest->getQueryParams(); // [ 'product.color' => 'red' ]

        return new Response('');
    }
}
```

## Code quality, tests and benchmarks

Every time changes are introduced into the library, [Github](https://github.com/loophp/unaltered-psr-http-message-bridge-bundle/actions) run the tests and the benchmarks.

The library has tests written with [PHPSpec](http://www.phpspec.net/).
Feel free to check them out in the `spec` directory. Run `composer phpspec` to trigger the tests.

Before each commit some inspections are executed with [GrumPHP](https://github.com/phpro/grumphp), run `./vendor/bin/grumphp run` to check manually.

[PHPInfection](https://github.com/infection/infection) is used to ensure that your code is properly tested, run `composer infection` to test your code.

## Contributing

Feel free to contribute to this library by sending Github pull requests. I'm quite reactive :-)
