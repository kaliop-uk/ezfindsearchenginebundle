<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

class ContentId extends SortClauseHandler
{
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\ContentId;
    }

    public function handle(SortClause $sortClause)
    {
        return ["id" => $this->getDirection($sortClause)];
    }
}
