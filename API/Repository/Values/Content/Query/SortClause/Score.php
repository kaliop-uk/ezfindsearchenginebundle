<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Used to sort by relevance
 */
class Score extends SortClause
{
    /**
     * @param string $sortDirection
     */
    public function __construct($sortDirection = Query::SORT_ASC)
    {
        parent::__construct('score', $sortDirection);
    }
}
