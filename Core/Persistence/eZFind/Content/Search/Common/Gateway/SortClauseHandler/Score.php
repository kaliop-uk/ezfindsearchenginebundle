<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\SortClause as KSortClause;

class Score extends SortClauseHandler
{
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof KSortClause\Score;
    }

    public function handle(SortClause $sortClause)
    {
        return [
            'score' => $this->getDirection($sortClause),
        ];
    }
}
