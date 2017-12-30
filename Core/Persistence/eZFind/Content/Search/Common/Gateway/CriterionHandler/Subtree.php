<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class Subtree extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\Subtree;
    }

    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        /*return '(' .
            implode(
                ' AND ',
                array_map(
                    function ($value) {
                        return 'meta_path_string_ms:' . str_replace('/', '\\/', $value) . '*';
                    },
                    $criterion->value
                )
            ) .
            ')';*/
        $result = [];

        $valueList = (array)$criterion->value;

        foreach ($valueList as $value) {
            /// @todo is this correct? should we use 'path' instead ?
            $result[] = 'meta_path_string_ms:' . str_replace('/', '\\/', $value) . '*';
        }

        return count($result) == 1 ? $result[0] : array_unshift($result, 'OR');
    }
}
