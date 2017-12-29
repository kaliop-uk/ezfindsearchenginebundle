<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Kaliop\EzFindSearchEngineBundle\Core\Base\Exceptions\NotImplementedException;

class SortClauseConverter extends Converter
{
    /**
     * @param SortClause $sortClause
     * @return bool
     */
    public function accept(SortClause $sortClause)
    {
        /** @var SortClauseHandler $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->accept($sortClause)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SortClause $sortClause
     * @return array
     * @throws NotImplementedException
     */
    public function handle(SortClause $sortClause)
    {
        /** @var SortClauseHandler $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->accept($sortClause)) {
                return $handler->handle($sortClause);
            }
        }

        throw new NotImplementedException(
            'No handler available for: ' . get_class($sortClause) . ' with direction ' . $sortClause->direction
        );
    }
}
