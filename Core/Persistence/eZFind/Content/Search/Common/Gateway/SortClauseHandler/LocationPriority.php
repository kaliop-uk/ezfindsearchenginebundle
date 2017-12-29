<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

class LocationPriority extends SortClauseHandler
{
    public function accept(SortClause $sortClause)
    {
        // Handling both deprecated priority clause and the new one.
        return ($sortClause instanceof SortClause\Location\Priority) || ($sortClause instanceof SortClause\LocationPriority);
    }

    public function handle(SortClause $sortClause)
    {
        return [
            'main_node_meta_priority_si' => trim($this->getDirection($sortClause)),
        ];
    }
}
