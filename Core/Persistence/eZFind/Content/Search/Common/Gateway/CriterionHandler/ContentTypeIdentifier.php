<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class ContentTypeIdentifier extends CriterionHandler
{
    /**
     * @inheritdoc
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\ContentTypeIdentifier;
    }

    /**
     * @inheritdoc
     */
    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        $valueList = (array)$criterion->value;

        $classIdentifiers = [];
        foreach ($valueList as $value) {
            $classIdentifiers[] = $this->escapeValue($value);
        }

        return 'class_identifier:(' . implode(' OR ', $classIdentifiers) . ')';
    }
}
