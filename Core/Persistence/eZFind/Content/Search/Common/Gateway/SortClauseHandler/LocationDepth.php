<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

class LocationDepth extends SortClauseHandler
{
    public function accept(SortClause $sortClause)
    {
        // Handling both deprecated priority clause and the new one.
        return ($sortClause instanceof SortClause\Location\Depth) || ($sortClause instanceof SortClause\LocationDepth);
    }

    public function handle(SortClause $sortClause)
    {
        return [
            'main_node_meta_depth_si' => $this->getDirection($sortClause),
        ];
    }
}
