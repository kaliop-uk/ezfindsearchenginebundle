<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion as BaseCriterion;

class SolrRaw extends BaseCriterion
{
    public function __construct($value)
    {
        $this->value = $value;
    }
}