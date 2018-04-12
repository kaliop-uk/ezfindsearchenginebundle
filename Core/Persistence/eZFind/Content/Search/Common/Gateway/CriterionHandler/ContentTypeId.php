<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class ContentTypeId extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\ContentTypeId;
    }

    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        $valueList = (array)$criterion->value;

        $classes = [];
        foreach ($valueList as $value) {
            $classes[] = $this->escapeValue($value);
        }

        return 'contentclass_id:(' . implode(' OR ', $classes) . ')';
    }
}
