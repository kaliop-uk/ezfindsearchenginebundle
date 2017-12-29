<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Base\Exceptions\NotImplementedException;

class CriteriaConverter extends Converter
{
    /**
     * @param Criterion $criterion
     * @return bool
     */
    public function canHandle(Criterion $criterion)
    {
        /** @var CriterionHandler $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->accept($criterion)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Criterion $criterion
     * @return mixed
     * @throws NotImplementedException
     */
    public function handle(Criterion $criterion)
    {
        /** @var CriterionHandler $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->accept($criterion)) {
                return $handler->handle($this, $criterion);
            }
        }

        throw new NotImplementedException(
            'No handler available for: ' . get_class($criterion) . ' with operator ' . $criterion->operator
        );
    }
}
