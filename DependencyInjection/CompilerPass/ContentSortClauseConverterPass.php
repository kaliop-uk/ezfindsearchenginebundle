<?php

namespace Kaliop\EzFindSearchEngineBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ContentSortClauseConverterPass extends ConverterPass
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('ezfind_search_engine.content.sort_clause_converter')) {
            $this->addHandlersToService(
                $container,
                'ezfind_search_engine.content.sort_clause_converter',
                'ezfind_search_engine.content.sort_clause_handler'
            );
        }
    }
}
