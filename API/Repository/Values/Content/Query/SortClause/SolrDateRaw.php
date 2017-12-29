<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Can be used to sort on a 'datetime' content field
 */
class SolrDateRaw extends SolrRaw
{
    /**
     * SolrDateRaw constructor.
     * @param string $field the name of the field to sort on
     * @param string $sortDirection
     */
    public function __construct($field, $sortDirection = Query::SORT_ASC)
    {
        parent::__construct("attr_" . $field . "_dt", $sortDirection);
    }
}
