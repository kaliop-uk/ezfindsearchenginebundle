<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

class DatePublished extends SortClauseHandler
{
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\DatePublished;
    }

    public function handle(SortClause $sortClause)
    {
        return [
            'published' => $this->getDirection($sortClause),
        ];
    }
}
