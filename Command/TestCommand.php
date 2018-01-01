<?php

namespace Kaliop\EzFindSearchEngineBundle\Command;

use eZ\Publish\API\Repository\Values\Content\Content;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query as KQuery;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\SortClause as KSortClause;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\Criterion as KCriterion;
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
        $query->query = new Criterion\LogicalOr([
            new Criterion\LogicalAnd([
                new Criterion\ContentTypeIdentifier('fs_article'),
                new Criterion\Subtree('/1/2/'),
                new Criterion\ParentLocationId(128),
                new Criterion\FullText('spuerscript'),
                new Criterion\Field('fs_article/strapline', Criterion\Operator::CONTAINS, 'ome'),
                new Criterion\Field('fs_article/strapline', Criterion\Operator::LIKE, 's_m%'),
                new Criterion\Field('fs_article/integer', Criterion\Operator::GT, 12),
                new Criterion\Field('fs_article/integer', Criterion\Operator::IN, array(23, 32)),
            ]),
            new Criterion\ContentTypeIdentifier('folder'),
            new Criterion\LogicalAnd([
                new Criterion\SectionId(1),
                new Criterion\ContentId(5),
                new Criterion\LocationId(5),
                new Criterion\LocationPriority(Criterion\Operator::GT, 0),
                new Criterion\RemoteId('abcdefg:ka and bb:cc'),
                new Criterion\DateMetadata(Criterion\DateMetadata::CREATED, Criterion\Operator::GT, 500000000),
                new KCriterion\SolrRaw('NOT(meta_path_string_ms:bbbb^4 OR meta_path_string_ms:bbba^4 AND (meta_path_string_ms:hello OR meta_path_string_ms:world))'),
                new Criterion\LogicalNot(new Criterion\RemoteId('abcdefg')),
                new Criterion\UserMetadata(Criterion\UserMetadata::OWNER, Criterion\Operator::EQ, 14),
            ])
        ]);

        $query->limit = 10;
        //$query->offset = $searchOffset;

        $query->sortClauses = [
            //new KSortClause\Score(KQuery::SORT_DESC)
            //new SortClause\ContentId(KQuery::SORT_ASC)
            new SortClause\LocationPathString(KQuery::SORT_DESC)
        ];

        //$query->returnType = KQuery::RETURN_EZFIND_DATA;

        $results = $searchService->findContent($query);

        echo "count: "; var_dump(count($results->searchHits));
        echo "totalCount: "; var_dump($results->totalCount);
        echo "time (ms): "; var_dump($results->time);
        echo "maxScore: ";  var_dump($results->maxScore);
        echo "q: "; var_dump($results->searchExtras->attribute('responseHeader')['params']['q']);
        echo "fq: "; var_dump($results->searchExtras->attribute('responseHeader')['params']['fq']);
        echo "sort: "; var_dump($results->searchExtras->attribute('responseHeader')['params']['sort']);

        foreach($results->searchHits as $i => $searchHit) {
            /** @var Content $content */
            $content = $searchHit->valueObject;
            $score = $searchHit->score;
            //echo "hit $i: obj ".$content->id.', score '.$score."\n";
            var_dump($content);
        }

        //var_dump($results->searchExtras);
    }
}
