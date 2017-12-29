<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

abstract class SortClauseHandler implements Handler
{
    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    abstract public function accept(SortClause $sortClause);

    /**
     * Map field value to a proper SOLR representation
     *
     * @param SortClause $sortClause
     *
     * @return array
     */
    abstract public function handle(SortClause $sortClause);

    /**
     * Get Solr sort direction from sort clause.
     *
     * @param SortClause $sortClause
     *
     * @return string
     */
    protected function getDirection(SortClause $sortClause)
    {
        return $sortClause->direction === Query::SORT_DESC ? 'desc' : 'asc';
    }
}
