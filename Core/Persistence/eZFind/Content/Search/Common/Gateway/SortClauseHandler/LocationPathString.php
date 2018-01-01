<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

class LocationPathString extends SortClauseHandler
{
    public function accept(SortClause $sortClause)
    {
        // Handling both deprecated priority clause and the new one.
        return ($sortClause instanceof SortClause\LocationPathString);
    }

    public function handle(SortClause $sortClause)
    {
        return [
            'main_node_meta_path_string_ms' => $this->getDirection($sortClause),
        ];
    }
}
