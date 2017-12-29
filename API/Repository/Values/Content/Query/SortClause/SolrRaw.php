<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Can be used to sort on any Solr field
 */
class SolrRaw extends SortClause
{
    /**
     * @param string $field
     * @param string $sortDirection
     */
    public function __construct($field, $sortDirection = Query::SORT_ASC)
    {
        parent::__construct($field, $sortDirection);
    }
}
