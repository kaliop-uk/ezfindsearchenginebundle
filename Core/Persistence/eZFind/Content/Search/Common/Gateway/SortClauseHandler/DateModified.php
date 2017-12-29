<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

class DateModified extends SortClauseHandler
{
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\DateModified;
    }

    public function handle(SortClause $sortClause)
    {
        return [
            'modified' => trim($this->getDirection($sortClause)),
        ];
    }
}
