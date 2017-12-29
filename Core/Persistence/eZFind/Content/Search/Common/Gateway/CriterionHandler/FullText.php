<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText as BaseFullText;

class FullText extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof BaseFullText;
    }

    /// @todo fix this
    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        // Sanitize the value
        $cleanSearchQuery = preg_replace('/["]?([[:alpha:]]{2}\d{3}\-\d{6})["]?/', '"$1"', $criterion->value);
        return $cleanSearchQuery;
    }
}
