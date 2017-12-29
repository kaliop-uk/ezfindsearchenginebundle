<?php

namespace Kaliop\EzFindSearchEngineBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Kaliop\EzFindSearchEngineBundle\DependencyInjection\CompilerPass;

class KaliopEzFindSearchEngineBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CompilerPass\ContentCriteriaConverterPass());
        $container->addCompilerPass(new CompilerPass\ContentSortClauseConverterPass());
    }
}
