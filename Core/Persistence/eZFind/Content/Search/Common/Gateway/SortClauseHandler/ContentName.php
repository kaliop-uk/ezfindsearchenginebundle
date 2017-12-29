<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

class ContentName extends SortClauseHandler
{
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\ContentName;
    }

    public function handle(SortClause $sortClause)
    {
        // We trim the direction because the parent class adds a space (assuming we're returning a flat string)
        return ["name" => trim($this->getDirection($sortClause))];
    }
}
