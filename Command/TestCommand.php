<?php

namespace Kaliop\EzFindSearchEngineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query as KQuery;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\SortClause as KSortClause;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('kaliop:eseb:test')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // HACK to make sure ezfind modules and fetch functions are active.
        // The problem still remains though: the rest of the legacy kernel will not be loaded correctly...
        $legacyKernelClosure = $this->getContainer()->get('ezpublish_legacy.kernel');
        $legacyKernel = $legacyKernelClosure();
        $legacyKernel->runCallback(
            function ()  {
                $moduleRepositories = \eZModule::activeModuleRepositories(true);
                $moduleRepositories[] = 'extension/ezfind/modules';
                //var_dump($moduleRepositories);
                \eZModule::setGlobalPathList( $moduleRepositories );
            },
            false
        );

        $searchService = $this->getContainer()->get('ezfind_search_engine.api.service.search');

        $query = new KQuery();
        $query->query = new Criterion\LogicalAnd([
            new Criterion\ContentTypeIdentifier('article'),
            new Criterion\FullText('*')
        ]);

        $query->limit = 2;
        //$query->offset = $searchOffset;

        /*if (strtolower($sortOrder) == 'score') {
            $query->sortClauses = [
                new KSortClause\Score(KQuery::SORT_DESC)
            ];
        } else {
            $query->sortClauses = [
                new SortClause\Field(Article::CONTENT_TYPE_IDENTIFIER, 'publication_date', KQuery::SORT_DESC),
            ];
        }*/

        $results = $searchService->findContent($query);

        var_dump($results->totalCount);
        var_dump($results->time);
        var_dump($results->maxScore);
        var_dump($results->searchExtras);
    }
}
