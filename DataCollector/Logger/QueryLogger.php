<?php

namespace Kaliop\EzFindSearchEngineBundle\DataCollector\Logger;

use ezfSearchResultInfo;

class QueryLogger
{
    /**
     * @var ezfSearchResultInfo[]
     */
    protected $resultsInfo;

    /**
     * QueryLogger constructor.
     */
    public function __construct()
    {
        $this->resultsInfo = [];
    }

    /**
     * Add new eZSearchResultInfo into query logger to gather extra data.
     *
     * @param ezfSearchResultInfo $resultInfo
     */
    public function addResultsInfo(ezfSearchResultInfo $resultInfo)
    {
        $this->resultsInfo[] = $resultInfo;
    }

    /**
     * Get number of successful SOLR queries.
     *
     * @return int
     */
    public function getQueriesNumber()
    {
        $validCalls = 0;

        foreach ($this->resultsInfo as $resultInfo) {
            $responseHeader = $resultInfo->attribute('responseHeader');
            if (isset($responseHeader['status']) && $responseHeader['status'] == 0) {
                $validCalls++;
            }

        }
        return $validCalls;
    }

    /**
     * Get number of invalid SOLR queries.
     *
     * @return int
     */
    public function getInvalidQueriesNumber()
    {
        return (count($this->resultsInfo) - $this->getQueriesNumber());
    }

    /**
     * Get total time in milliseconds of all SOLR queries made.
     *
     * @return int
     */
    public function getTotalTime()
    {
        $totalTime = 0;

        foreach ($this->resultsInfo as $resultInfo) {
            $responseHeader = $resultInfo->attribute('responseHeader');
            $totalTime += intval($responseHeader['QTime']);
        }

        return $totalTime;
    }
}