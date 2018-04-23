<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class ParentLocationId extends CriterionHandler
{
    /**
     * @inheritdoc
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\ParentLocationId;

    }

    /**
     * @inheritdoc
     */
    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        $locationIds = [];
        $valueList = (array)$criterion->value;

        foreach ($valueList as $value) {
            $locationIds[] = $this->escapeValue($value);
        }

        return 'meta_main_parent_node_id_si:(' . implode(' OR ', $locationIds) . ')';
    }
}
