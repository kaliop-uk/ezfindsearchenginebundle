<?php

namespace Kaliop\EzFindSearchEngineBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

abstract class ConverterPass implements CompilerPassInterface
{
    protected function addHandlersToService(ContainerBuilder $container, $service, $taggedService)
    {
        /** @var Definition $criterionConverterDefinition */
        $criterionConverterDefinition = $container->findDefinition($service);

        $services = $container->findTaggedServiceIds($taggedService);

        foreach ($services as $id => $tags) {
            $criterionConverterDefinition->addMethodCall(
                'addHandler',
                [
                    new Reference($id),
                ]
            );
        }
    }

}
