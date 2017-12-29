<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use Kaliop\EzFindSearchEngineBundle\Core\Base\Exceptions\InvalidCriterionException;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;
use Kaliop\EzFindSearchEngineBundle\Persistence\Solr\Content\Search\CriterionVisitor;
use Kaliop\EzFindSearchEngineBundle\Persistence\Solr\Content\Search\CriterionVisitorDispatcher as Dispatcher;

class LogicalNot extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\LogicalNot;
    }

    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        if (!isset($criterion->criteria[0]) ||
            count($criterion->criteria) > 1
        ) {
            throw new InvalidCriterionException("LogicalNot can receive one criteria only");
        }

        $result = trim($converter->convertCriteria($criterion->criteria[0]),'()');
        $result = '-' . preg_replace('%(AND |OR )%', '$1-', $result);
        return $result;
    }
}
