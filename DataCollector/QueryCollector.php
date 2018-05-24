<?php

namespace Kaliop\EzFindSearchEngineBundle\DataCollector;

use Kaliop\EzFindSearchEngineBundle\DataCollector\Logger\QueryLogger;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class QueryCollector extends DataCollector
{
    /**
     * @var QueryLogger
     */
    protected $logger;

    /**
     * QueryCollector constructor.
     *
     * @param QueryLogger $logger
     */
    public function __construct(QueryLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'queries_number' => $this->logger->getQueriesNumber(),
            'invalid_queries' => $this->logger->getInvalidQueriesNumber(),
            'total_time' => $this->logger->getTotalTime(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ezfind_search_engine.query_collector';
    }

    /**
     * Get number of valid SOLR queries.
     *
     * @return int
     */
    public function getQueriesNumber()
    {
        return $this->data['queries_number'];
    }

    /**
     * Get number of invalid SOLR queries.
     *
     * @return int
     */
    public function getInvalidQueriesNumber()
    {
        return $this->data['invalid_queries'];
    }

    /**
     * Get total queries time in milliseconds.
     *
     * @return int
     */
    public function getTotalTime()
    {
        return $this->data['total_time'];
    }
}