<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class SectionId extends CriterionHandler
{
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\SectionId;
    }

    public function handle( CriteriaConverter $converter, Criterion $criterion )
    {
        $result = [];

        $valueList = (array)$criterion->value;

        foreach ($valueList as $value) {
            $result[] = 'section_id:' . $this->escapeValue($value);
        }

        return count($result) == 1 ? $result[0] : array_unshift($result, 'OR');
    }
}