<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\Criterion as KCriterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class SolrRaw extends CriterionHandler
{
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof KCriterion\SolrRaw;
    }

    public function handle( CriteriaConverter $converter, Criterion $criterion )
    {
        return $criterion->value;
    }
}
