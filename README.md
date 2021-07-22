[![Latest Stable Version][latest stable version]][1]
 [![GitHub stars][github stars]][1]
 [![Total Downloads][total downloads]][1]
 [![GitHub Workflow Status][github workflow status]][2]
 [![Scrutinizer code quality][code quality]][3]
 [![Type Coverage][type coverage]][4]
 [![Code Coverage][code coverage]][3]
 [![License][license]][1]
 [![Donate!][donate github]][5]
 [![Donate!][donate paypal]][6]

# Unaltered PSR HTTP Message Bridge Bundle

An opt-in and drop-in replacement bundle for [symfony/psr-http-message-bridge](https://github.com/symfony/psr-http-message-bridge)
that doesn't alter the query parameters.

This package register a decorator for the service `PsrHttpFactory` in your Symfony application.

The only difference with the original class from [symfony/psr-http-message-bridge](https://github.com/symfony/psr-http-message-bridge) is that it doesn't alter the query parameters when converting a Symfony request
into a PSR7 request.

Context:

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

# Usage

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

final class MainController {
    /**
     * @Route("/api/offers", name="api_offers")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(
        Request $request,
        HttpMessageFactoryInterface $httpMessageFactory,
        RequestInterface $psrRequest
    ): Response {
        // Using Symfony's request object.
        $uri = $request->getUri(); // http://localhost:8000/api/offers?product_color=red
        $params = $request->query->all(); // [ 'product_color' => 'red' ]

        // Using PSR Request.
        $psrRequest = $httpMessageFactory->createRequest($request);
        $uri = (string) $psrRequest->getUri(); // http://localhost:8000/api/offers?product.color=red
        $params = $psrRequest->getUri()->getQuery(); // 'product.color=red'

        // Or directly by requesting the PSR request through RequestInterface parameter.

        return new Response('');
    }
}
```

Notice that the query string parameters has been altered, from `field.filter` to `field_filter` when using the
Symfony Request object.

# Configuration

There is no configuration and you do not have to do anything besides requiring this package in your application.

## Code quality, tests and benchmarks

Every time changes are introduced into the library, [Github](https://github.com/loophp/unaltered-psr-http-message-bridge-bundle/actions) run the tests and the benchmarks.

The library has tests written with [PHPSpec](http://www.phpspec.net/).
Feel free to check them out in the `spec` directory. Run `composer phpspec` to trigger the tests.

Before each commit some inspections are executed with [GrumPHP](https://github.com/phpro/grumphp), run `./vendor/bin/grumphp run` to check manually.

[PHPInfection](https://github.com/infection/infection) is used to ensure that your code is properly tested, run `composer infection` to test your code.

## Contributing

Feel free to contribute by sending Github pull requests. I'm quite responsive :-)

## Changelog

See [CHANGELOG.md][15] for a changelog based on [git commits][16].

For more detailed changelogs, please check [the release changelogs][17].

[1]: https://packagist.org/packages/loophp/unaltered-psr-http-message-bridge-bundle
[2]: https://github.com/loophp/unaltered-psr-http-message-bridge-bundle/actions
[latest stable version]: https://img.shields.io/packagist/v/loophp/unaltered-psr-http-message-bridge-bundle.svg?style=flat-square
[github stars]: https://img.shields.io/github/stars/loophp/unaltered-psr-http-message-bridge-bundle.svg?style=flat-square
[total downloads]: https://img.shields.io/packagist/dt/loophp/unaltered-psr-http-message-bridge-bundle.svg?style=flat-square
[github workflow status]: https://img.shields.io/github/workflow/status/loophp/unaltered-psr-http-message-bridge-bundle/Unit%20tests?style=flat-square
[code quality]: https://img.shields.io/scrutinizer/quality/g/loophp/unaltered-psr-http-message-bridge-bundle/master.svg?style=flat-square
[3]: https://scrutinizer-ci.com/g/loophp/unaltered-psr-http-message-bridge-bundle/?branch=master
[type coverage]: https://img.shields.io/badge/dynamic/json?style=flat-square&color=color&label=Type%20coverage&query=message&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Floophp%2Funaltered-psr-http-message-bridge-bundle%2Fcoverage
[4]: https://shepherd.dev/github/loophp/unaltered-psr-http-message-bridge-bundle
[code coverage]: https://img.shields.io/scrutinizer/coverage/g/loophp/unaltered-psr-http-message-bridge-bundle/master.svg?style=flat-square
[license]: https://img.shields.io/packagist/l/loophp/unaltered-psr-http-message-bridge-bundle.svg?style=flat-square
[donate github]: https://img.shields.io/badge/Sponsor-Github-brightgreen.svg?style=flat-square
[donate paypal]: https://img.shields.io/badge/Sponsor-Paypal-brightgreen.svg?style=flat-square
[5]: https://github.com/sponsors/drupol
[6]: https://www.paypal.me/drupol
[10]: https://github.com/symfony/psr-http-message-bridge
[11]: https://github.com/loophp/unaltered-psr-http-message-bridge-bundle/actions
[12]: http://www.phpspec.net/
[13]: https://github.com/phpro/grumphp
[14]: https://github.com/infection/infection
[15]: https://github.com/phpstan/phpstan
[16]: https://github.com/vimeo/psalm
[15]: https://github.com/loophp/unaltered-psr-http-message-bridge-bundle/blob/master/CHANGELOG.md
[16]: https://github.com/loophp/unaltered-psr-http-message-bridge-bundle/commits/master
[17]: https://github.com/loophp/unaltered-psr-http-message-bridge-bundle/releases
