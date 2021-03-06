<?php

declare(strict_types=1);

namespace loophp\UnalteredPsrHttpMessageBridgeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class UnalteredPsrHttpMessageBridgeExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @phpstan-ignore-next-line
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../Resources/config')
        );

        $loader->load('services.yaml');
    }
}
