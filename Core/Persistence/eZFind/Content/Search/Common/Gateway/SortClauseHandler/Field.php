<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseHandler;

class Field extends SortClauseHandler
{
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\Field;
    }

    public function handle(SortClause $sortClause)
    {
        $fieldIdentifier = $sortClause->targetData->typeIdentifier . '/' . $sortClause->targetData->fieldIdentifier;
        return [
            $fieldIdentifier => $this->getDirection($sortClause),
        ];
    }
}
