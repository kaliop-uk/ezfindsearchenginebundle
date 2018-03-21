<?php

namespace Kaliop\EzFindSearchEngineBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContentFacetConverterPass extends ConverterPass
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has('ezfind_search_engine.content.facet_converter')) {
            $this->addHandlersToService(
                $container,
                'ezfind_search_engine.content.facet_converter',
                'ezfind_search_engine.content.facet_handler'
            );
        }
    }
}
