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

    /**
     * Given a Solr filter in array form (as passed to ezfeZPSolrQueryBuilder), return its string representtaion
     * @param array $filter
     * @return string
     * @throws NotImplementedException
     *
     * @todo allow class ezfeZPSolrQueryBuilder to be specified via settings
     */
    public function generateQueryString($filter)
    {
        // build the Solr query string via ezfind - we have to resort to
        // a hackish way to access a protected method in ezfeZPSolrQueryBuilder
        $queryBuilderClass = '\ezfeZPSolrQueryBuilder';
        /** @var \ezfeZPSolrQueryBuilder $queryBuilder */
        $queryBuilder = new $queryBuilderClass(null);
        $filterCreator = \Closure::bind(function($filter){return $this->getParamFilterQuery(array('Filter' => $filter));}, $queryBuilder, $queryBuilder);
        return $filterCreator($filter);
    }

}
