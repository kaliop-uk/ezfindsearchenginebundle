<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;
use Kaliop\EzFindSearchEngineBundle\Persistence\Solr\Content\Search\CriterionVisitor;
use Kaliop\EzFindSearchEngineBundle\Persistence\Solr\Content\Search\CriterionVisitorDispatcher;

class FieldIn extends FieldBase
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\Field &&
            in_array($criterion->operator, [Operator::IN, Operator::EQ, Operator::CONTAINS]);
    }

    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        $fieldName = $this->determineFieldName($criterion->target);
        $criterionValue = $criterion->value;

        if (!is_array($criterionValue)) {
            return $fieldName . ':' . $criterionValue;
        }

        return $fieldName . ':(' . implode(
                $criterionValue, ' '
            ) . ')';
    }
}
