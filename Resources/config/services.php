<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use loophp\UnalteredPsrHttpMessageBridgeBundle\Factory\UnalteredPsrHttpFactory;
use Psr\Http\Message\RequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

return static function (ContainerConfigurator $container) {
    $container
        ->services()
        ->defaults()
        ->autoconfigure(true)
        ->autowire(true);

    $container
        ->services()
        ->set(Request::class)
        ->factory([service(RequestStack::class), 'getCurrentRequest']);

    $container
        ->services()
        ->set(HttpFoundationFactory::class)
        ->autoconfigure(true)
        ->autowire(true);

    $container
        ->services()
        ->alias(HttpFoundationFactoryInterface::class, HttpFoundationFactory::class);

    $container
        ->services()
        ->set(PsrHttpFactory::class)
        ->autoconfigure(true)
        ->autowire(true);

    $container
        ->services()
        ->alias(HttpMessageFactoryInterface::class, PsrHttpFactory::class);

    $container
        ->services()
        ->set(RequestInterface::class)
        ->factory([service(PsrHttpFactory::class), 'createRequest'])
        ->arg(
            '$symfonyRequest',
            service(Request::class)
        );

    $container
        ->services()
        ->set(UnalteredPsrHttpFactory::class)
        ->decorate(PsrHttpFactory::class)
        ->arg(
            '$httpMessageFactory',
            service('.inner')
        );
};
