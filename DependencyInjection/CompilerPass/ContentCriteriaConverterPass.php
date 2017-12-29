<?php

namespace Kaliop\EzFindSearchEngineBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContentCriteriaConverterPass extends ConverterPass
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('ezfind_search_engine.content.criteria_converter.logical')) {
            $this->addHandlersToService(
                $container,
                'ezfind_search_engine.content.criteria_converter.logical',
                'ezfind_search_engine.content.criterion_handler.logical'
            );
        }

        if ($container->has('ezfind_search_engine.content.criteria_converter.content_type')) {
            $this->addHandlersToService(
                $container,
                'ezfind_search_engine.content.criteria_converter.content_type',
                'ezfind_search_engine.content.criterion_handler.content_type'
            );
        }

        if ($container->has('ezfind_search_engine.content.criteria_converter.subtree')) {
            $this->addHandlersToService(
                $container,
                'ezfind_search_engine.content.criteria_converter.subtree',
                'ezfind_search_engine.content.criterion_handler.subtree'
            );
        }

        if ($container->has('ezfind_search_engine.content.criteria_converter.filter')) {
            $this->addHandlersToService(
                $container,
                'ezfind_search_engine.content.criteria_converter.filter',
                'ezfind_search_engine.content.criterion_handler.filter'
            );
        }

        if ($container->has('ezfind_search_engine.content.criteria_converter.query_string')) {
            $this->addHandlersToService(
                $container,
                'ezfind_search_engine.content.criteria_converter.query_string',
                'ezfind_search_engine.content.criterion_handler.query_string'
            );
        }
    }
}
