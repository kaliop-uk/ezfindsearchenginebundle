<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class ContentTypeIdentifier extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\ContentTypeIdentifier;
    }

    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        $result = [];

        $valueList = (array)$criterion->value;

        foreach ($valueList as $value) {
            $result[] = 'class_identifier:' . $value;
        }

        return count($result) == 1 ? $result[0] : array_unshift($result, 'OR');
    }
}
