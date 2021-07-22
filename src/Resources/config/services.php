<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use loophp\UnalteredPsrHttpMessageBridgeBundle\Factory\UnalteredPsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services
        ->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(UnalteredPsrHttpFactory::class)
        ->decorate(PsrHttpFactory::class)
        ->arg(
            '$httpMessageFactory',
            service('.inner')
        );
};
