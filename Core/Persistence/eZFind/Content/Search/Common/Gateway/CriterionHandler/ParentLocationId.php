<?php


namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

/// @todo implement ???
class ParentLocationId extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\ParentLocationId &&
            (($criterion->operator ?: Criterion\Operator::IN) === Criterion\Operator::IN ||
                $criterion->operator === Criterion\Operator::EQ);

    }

    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        $result = [];

        if (!is_array($criterion->value)) {
            $valueList = [$criterion->value];
        } else {
            $valueList = $criterion->value;
        }

        foreach ($valueList as $value) {
            $result[] = true;
        }

        return $result;
    }

}
