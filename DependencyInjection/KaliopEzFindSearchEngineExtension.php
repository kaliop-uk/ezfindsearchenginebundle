<?php

namespace Kaliop\EzFindSearchEngineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class KaliopEzFindSearchEngineExtension extends ConfigurableExtension
{
    public static $loadTestConfig = false;

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (self::$loadTestConfig != false) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Tests/ezpublish/config'));
            $loader->load('services.yml');
        }
    }
}
