<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Repository;

use Closure;
use ezfSearchResultInfo;
use Psr\Log\LoggerInterface;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as CoreNotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query as KaliopQuery;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Search\SearchResult as KaliopSearchResult;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Search\SearchHit;
use Kaliop\EzFindSearchEngineBundle\Core\Base\Exceptions\NotImplementedException;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\SortClauseConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Base\Exceptions\eZFindException;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetConverter;
use Kaliop\EzFindSearchEngineBundle\DataCollector\Logger\QueryLogger;

/// @todo implement LoggerTrait or similar interface
class SearchService implements SearchServiceInterface
{
    const DEFAULT_LIMIT = 20;

    const FETCH_ALL = 2147483647; // Max 32-bit signed Integer (limited by SOLR)

    protected $ezFindModule;

    protected $ezFindFunction;

    /** @var Closure */
    protected $legacyKernelClosure;

    /** @var ContentService */
    protected $contentService;

    /** @var ContentTypeService */
    protected $contentTypeService;

    /** @var CriteriaConverter */
    protected $filterCriteriaConverter;

    /** @var  FacetConverter */
    protected $facetConverter;

    /** @var SortClauseConverter */
    protected $sortClauseConverter;

    protected $defaultBoostFunctions;

    protected $defaultFieldsToReturn;

    protected $defaultReturnType;

    protected $defaultQueryHandler = 'ezpublish';

    protected $defaultEnableElevation = true;

    protected $defaultForceElevation = false;

    /** @var bool */
    protected $throwErrors;

    /** @var LoggerInterface */
    protected $logger;

    /** @var QueryLogger */
    protected $queryLogger;

    public function __construct(
        Closure $legacyKernelClosure,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        CriteriaConverter $filterCriteriaConverter,
        FacetConverter $facetConverter,
        SortClauseConverter $sortClauseConverter,
        $defaultBoostFunctions,
        $defaultFieldsToReturn,
        $defaultReturnType = KaliopQuery::RETURN_CONTENTS,
        $ezFindModule = 'ezfind',
        $ezFindFunction = 'search',
        $throwErrors = true,
        LoggerInterface $logger = null,
        QueryLogger $queryLogger = null
    ) {
        $this->legacyKernelClosure = $legacyKernelClosure;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->defaultReturnType = $defaultReturnType;
        $this->ezFindModule = $ezFindModule;
        $this->ezFindFunction = $ezFindFunction;
        $this->throwErrors = $throwErrors;
        $this->logger = $logger;
        $this->queryLogger = $queryLogger;

        // Converters
        $this->filterCriteriaConverter = $filterCriteriaConverter;
        $this->facetConverter = $facetConverter;
        $this->sortClauseConverter = $sortClauseConverter;

        // Making sure these are arrays
        $this->defaultBoostFunctions = (array)$defaultBoostFunctions;
        $this->defaultFieldsToReturn = (array)$defaultFieldsToReturn;
    }

    /**
     * @todo fill in the remaining members: timedOut, spellSuggestion
     *
     * @inheritdoc
     */
    public function findContent(Query $query, array $fieldFilters = [], $filterOnUserPermissions = true)
    {
        $result = $this->performSearch($query, $fieldFilters, $filterOnUserPermissions);

        $maxScore = null;
        $time = null;

        // q: is there any case where SearchExtras is not set or of a different type?
        if (isset($result['SearchExtras']) && $result['SearchExtras'] instanceof ezfSearchResultInfo) {
            /** @var ezfSearchResultInfo $extras */
            $extras = $result['SearchExtras'];
            $responseHeader = $extras->attribute('responseHeader');
            $time = $responseHeader['QTime'];

            // trick to access data from a protected member of ezfSearchResultInfo
            // @see http://blag.kazeno.net/development/access-private-protected-properties
            $propGetter = Closure::bind(function($prop){return $this->$prop;}, $extras, $extras);
            $resultArray = $propGetter('ResultArray');

            if (isset($resultArray['response']['maxScore'])) {
                $maxScore = $resultArray['response']['maxScore'];
            }

            // optimize: remove from SearchExtras 'response' to save memory using the 'Closure::bind' hack
            /// @todo (!important) make the cutover limit configurable
            if ($result['SearchCount'] > 100) {
                $resultsCleaner = Closure::bind(function(){unset($this->ResultArray['response']['docs']);}, $extras, $extras);
                $resultsCleaner();
            }
        }

        return new KaliopSearchResult(
            [
                'facets' => $result['Facets'],
                'searchHits' => $result['SearchHits'],
                'time' => $time,
                'maxScore' => $maxScore,
                'totalCount' => $result['SearchCount'],
                'searchExtras' => $result['SearchExtras'],
            ]
        );
    }

    /**
     * @todo Implement this method shrinking the fieldstoreturn to the bare minimum needed to build contentInfo
     *       without having to query the database
     * @since 5.4.5
     */
    public function findContentInfo(Query $query, array $languageFilter = [], $filterOnUserPermissions = true)
    {
        throw new NotImplementedException('Intentionally not implemented');
    }

    /**
     * @todo disable asking the total count for speed if possible
     *
     * @inheritdoc
     */
    public function findSingle(Criterion $criterion, array $fieldFilters = [], $filterOnUserPermissions = true)
    {
        $query = new Query();
        $query->criterion = $criterion;
        $query->limit = 1;
        $query->offset = 0;

        $result = $this->performSearch($query, $fieldFilters, $filterOnUserPermissions, true);

        if (!$result['SearchCount']) {
            throw new CoreNotFoundException('Content', 'findSingle() found no content for given criterion');
        } else {
            if ($result['SearchCount'] > 1) {
                throw new InvalidArgumentException(
                    'totalCount',
                    'findSingle() found more then one item for given criterion'
                );
            }
        }

        return reset($result['SearchHits']);
    }

    public function suggest($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null)
    {
        throw new NotImplementedException('Intentionally not implemented');
    }

    public function findLocations(LocationQuery $query, $languageFilter = [], $filterOnUserPermissions = true)
    {
        throw new NotImplementedException('Intentionally not implemented');
    }

    /**
     * @return \ezpKernel
     */
    protected function getLegacyKernel()
    {
        $legacyKernelClosure = $this->legacyKernelClosure;

        return $legacyKernelClosure();
    }

    /**
     * Perform SOLR search query.
     *
     * @param Query $query
     * @param array $fieldFilters
     * @param bool $filterOnUserPermissions
     * @param null|string $forceReturnType  When set, it overrides both the service default and the query default
     *
     * @return array    The same as returned by \eZSolr::Search(), with added members SearchHits and Facets
     *
     * @throws eZFindException
     */
    protected function performSearch(
        Query $query,
        array $fieldFilters,
        $filterOnUserPermissions,
        $forceReturnType = null
    ) {
        $returnType = $this->getReturnType($query, $forceReturnType);

        $this->initializeQueryLimit($query);

        $searchParameters = $this->getLegacySearchParameters($query, $fieldFilters, $filterOnUserPermissions, $returnType);
//var_dump($searchParameters);
        /** @var array $searchResult */
        $searchResult = $this->getLegacyKernel()->runCallback(
            function () use ($searchParameters) {
                return \eZFunctionHandler::execute($this->ezFindModule, $this->ezFindFunction, $searchParameters);
            },
            false
        );

        if ($this->queryLogger) {
            $this->queryLogger->addResultsInfo($searchResult['SearchExtras']);
        }

        $this->logSearchErrors($searchResult);

        if ($this->throwErrors) {
            $this->throwIfSearchError($searchResult);
        }

        $searchResult['SearchHits'] = $this->buildResultObjects($searchResult, $returnType);
        $searchResult['Facets'] = $this->buildResultFacets($searchResult['SearchExtras'], $query->facetBuilders);

        return $searchResult;
    }

    /**
     * Initializes the query limit to ensure it is set.
     *
     * @param Query $query
     */
    protected function initializeQueryLimit(Query $query)
    {
        if ($query->limit < 0) {
            $query->limit = self::FETCH_ALL;
        } elseif ($query->limit === null) {
            $query->limit = self::DEFAULT_LIMIT;
        }
    }

    /**
     * @see \Solr::search
     * @see ezfind/modules/ezfind/function_definition.php
     * @todo should we handle $fieldFilters ?
     *
     * @param Query $query
     * @param array $fieldFilters
     * @param bool $filterOnUserPermissions
     * @param string $returnType
     * @return array
     */
    protected function getLegacySearchParameters(Query $query, array $fieldFilters, $filterOnUserPermissions, $returnType)
    {
        $searchParameters = [
            'offset' => $query->offset,
            'limit' => $query->limit,
            // When we are rebuilding eZ5 objects, no need to load custom fields from Solr.
            // This 'hack' is the way to get ezfind to generate the minimum field list, plus the score
            'fields_to_return' => $returnType == KaliopQuery::RETURN_CONTENTS ? array('meta_score_value:score') : $this->extractLegacyParameter('fields_to_return', $query),
            // we either load eZ5 objects or return solr data, no need to tell ez4 to load objects as well
            'as_objects' => false, //$this->extractLegacyParameter('as_objects', $query),
            'query_handler' => $this->extractLegacyParameter('query_handler', $query),
            'enable_elevation' => $this->extractLegacyParameter('enable_elevation', $query),
            'force_elevation' => $this->extractLegacyParameter('force_elevation', $query),
            'boost_functions' => $this->extractLegacyParameter('boost_functions', $query),
        ];

        $scoreSort = false;
        if ($query->sortClauses) {
            $searchParameters['sort_by'] = $this->extractSort($query->sortClauses);

            if (array_key_exists('score', $searchParameters['sort_by'])) {
                $scoreSort = true;
            }
        }
//var_dump($query->criterion);die();
        $criterionFilter = array();
        if ($query->criterion) {
            $criterionFilter = $this->extractFilter($query->criterion);
        }

        $filterFilter = array();
        if ($query->filter) {
            $filterFilter = $this->extractFilter($query->filter);
        }

        if ($query->facetBuilders) {
            $searchParameters['facet'] = $this->extractFacet($query->facetBuilders);
        }

        if ($this->isEzFindCriterion($query->criterion)) {
            $searchParameters['query'] = reset($criterionFilter);
            $searchParameters['filter'] = $filterFilter;
        } elseif ($scoreSort) {
            // since we are sorting by score, we need to generate the solr query, as that is what is used to calculate score
            $searchParameters['query'] = $this->filterCriteriaConverter->generateQueryString($criterionFilter);
            $searchParameters['filter'] = $filterFilter;
        } else {
            // since we are not sorting by score, no need to do complex stuff. Only use a solr filter, which should be faster
            $searchParameters['query'] = '';
            $searchParameters['filter'] = array_merge($criterionFilter, $filterFilter);
        }

        // If we need to filter on permissions, set this to null so eZFind will fill it in.
        // Otherwise an empty array prevents eZFind from applying limitations
        if ($filterOnUserPermissions) {
            $searchParameters['limitation'] = null;
        } else {
            $searchParameters['limitation'] = [];
        }

        return $searchParameters;
    }

    protected function extractLegacyParameter($paramName, Query $query)
    {
        switch ($paramName) {
            case 'boost_functions':
                return ($query instanceof KaliopQuery && is_array(
                        $query->boostFunctions
                    )) ? $query->boostFunctions : $this->defaultBoostFunctions;
            case 'enable_elevation':
                return ($query instanceof KaliopQuery) ? $query->enableElevation : $this->defaultEnableElevation;
            case 'fields_to_return':
                return ($query instanceof KaliopQuery && is_array(
                        $query->fieldsToReturn
                    )) ? $query->fieldsToReturn : $this->defaultFieldsToReturn;
            case 'force_elevation':
                return ($query instanceof KaliopQuery) ? $query->forceElevation : $this->defaultForceElevation;
            case 'query_handler':
                return ($query instanceof KaliopQuery) ? $query->queryHandler : $this->defaultQueryHandler;
        }
    }

    /**
     * Order of importance:
     * 1. override (function parameter)
     * 2. query member (if set)
     * 3. default for this service
     * @param Query $query
     * @param null|string $forceReturnType
     * @return string
     */
    protected function getReturnType(Query $query, $forceReturnType = null)
    {
        if ($forceReturnType !== null) {
            return $forceReturnType;
        }

        return ($query instanceof KaliopQuery && $query->returnType !== null) ? $query->returnType : $this->defaultReturnType;
    }

    /**
     * Returns true if there is a single search criterion of type EzFindText
     * @param Query\Criterion|Query\Criterion[] $criteria $criteria
     * @return bool
     */
    protected function isEzFindCriterion($criteria)
    {
        if (!is_array($criteria)) {
            $criteria = array($criteria);
        }

        return (count($criteria) == 1 && $criteria[0] instanceof KaliopQuery\Criterion\EzFindText);
    }

    /**
     * @param Query\Criterion|Query\Criterion[] $criteria
     * @return array
     * @throws NotImplementedException
     */
    protected function extractFilter($criteria)
    {
        if (!is_array($criteria)) {
            $criteria = array($criteria);
        }

        $result = [];

        foreach ($criteria as $criterion) {
            $result[] = $this->filterCriteriaConverter->handle($criterion);
        }

        return $result;
    }

    /**
     * Extract FacetBuilders into legacy eZFind facet array.
     *
     * @param Query\FacetBuilder[] $facetBuilders
     * @return array
     *
     * @throws NotImplementedException
     */
    protected function extractFacet($facetBuilders)
    {
        $facets = [];

        foreach ($facetBuilders as $facetBuilder) {
            $facets[] = $this->facetConverter->handle($facetBuilder);
        }

        return $facets;
    }

    protected function extractSort($sortClauses)
    {
        $result = [];

        foreach ($sortClauses as $clause) {
            $sortClause = $this->sortClauseConverter->handle($clause);
            if (!empty($sortClause)) {
                $result = array_merge($result, $sortClause);
            }
        }

        return $result;
    }

    protected function throwIfSearchError($searchResult)
    {
        if (!is_array($searchResult)) {
            throw new eZFindException('The legacy search result is not an array');
        }
//var_dump($searchResult);die();
        if (isset($searchResult['SearchExtras']) && $searchResult['SearchExtras'] instanceof ezfSearchResultInfo) {
            $errors = $searchResult['SearchExtras']->attribute('error');
            /// @todo what if $errors it is an empty string, an array with unexepcted members or not even an array ?
            if (is_string($errors)) {
                throw new eZFindException($errors);
            } elseif (is_array($errors) && isset($errors['msg']) && isset($errors['code'])) {
                throw new eZFindException($errors['msg'], $errors['code']);
            }
        }
    }

    protected function logSearchErrors($searchResult)
    {
        if (!is_array($searchResult)) {
            if ($this->logger) {
                $this->logger->error('The legacy search result is not an array');
            }

            return;
        }

        /// @todo allow the query not to return some of these
        if (!isset($searchResult['SearchResult']) || !isset($searchResult['SearchCount']) || !isset($searchResult['StopWordArray']) ||
            !isset($searchResult['SearchExtras']) || !($searchResult['SearchExtras'] instanceof ezfSearchResultInfo)
        ) {
            if ($this->logger) {
                $this->logger->error('The legacy search result array misses expected members');
            }

            return;
        }

        /** @var ezfSearchResultInfo $searchExtras */
        $searchExtras = $searchResult['SearchExtras'];
        $errors = $searchExtras->attribute('error');
        if (!empty($errors) && $this->logger) {
            $this->logger->error(print_r($errors, true));
        }
    }

    /**
     * @param array $searchResultsContainer
     * @param string $returnType
     * @return SearchHit[]|array depending on $returnObjects
     */
    protected function buildResultObjects($searchResultsContainer, $returnType)
    {
        if ($returnType == KaliopQuery::RETURN_CONTENTS || $returnType == KaliopQuery::RETURN_EZFIND_DATA) {
            $searchResults = $searchResultsContainer['SearchResult'];
        } else {
            // we need a little hack to be able to access data in protected members
            $extras = $searchResultsContainer['SearchExtras'];

            // trick to access data from a protected member of ezfSearchResultInfo
            // @see http://blag.kazeno.net/development/access-private-protected-properties
            $propGetter = Closure::bind(function($prop){return $this->$prop;}, $extras, $extras);
            $resultArray = $propGetter('ResultArray');
            $searchResults = $resultArray['response']['docs'];
        }

        if (!is_array($searchResults)) {
            return [];
        }

        $results = array();

        foreach ($searchResults as $index => $result) {
            switch($returnType) {

                case KaliopQuery::RETURN_CONTENTS:
                    try {
                        $results[$index] = new SearchHit(
                            [
                                'valueObject' => $this->contentService->loadContent($result['id']),
                                'score' => isset($result['score'])? $result['score'] : null,
                                'highlight' => isset($result['highlight'])? $result['highlight'] : null,
                                'elevated' => isset($result['elevated'])? $result['elevated'] : null,
                                /// @todo decide what is the correct value for 'index': guid, installation_id/guid ?
                                //'index' => isset($result['guid'])? $result['guid'] : null,
                            ]
                        );
                    } catch (NotFoundException $e) {
                        if ($this->logger) {
                            // Solr sometimes gets out of sync... make sure users don't see exceptions here
                            $message = sprintf(
                                "Can not access content corresponding to solr record with Content Id: %s, Main Location Id: %s\n%s\n%s",
                                $result['id'],
                                $result['main_node_id'],
                                $e->getMessage(),
                                $e->getTraceAsString()
                            );

                            $this->logger->warning($message);
                        }
                        unset($searchResults[$index]);
                    } catch (UnauthorizedException $e) {
                        /// @todo verify when/if this can happen...
                        if ($this->logger) {
                            $message = sprintf(
                                "Can not access content corresponding to solr record with Content Id: %s, Main Location Id: %s\n%s\n%s",
                                $result['id'],
                                $result['main_node_id'],
                                $e->getMessage(),
                                $e->getTraceAsString()
                            );

                            $this->logger->warning($message);
                        }
                        unset($searchResults[$index]);
                    }
                    break;

                case KaliopQuery::RETURN_EZFIND_DATA:
                case KaliopQuery::RETURN_SOLR_DATA:
                    $results[$index] = new SearchHit(
                        [
                            'valueObject' => $result,
                            'score' => isset($result['score'])? $result['score'] : null,
                            'highlight' => isset($result['highlight'])? $result['highlight'] : null,
                            'elevated' => isset($result['elevated'])? $result['elevated'] : null,
                            /// @todo decide what is the correct value for 'index': guid, installation_id/guid ?
                            //'index' => isset($result['guid'])? $result['guid'] : null,
                        ]
                    );
                    break;
            }
        }

        return $results;
    }

    /**
     * Create result facets based on SOLR returned results.
     *
     * @param ezfSearchResultInfo $searchResultInfo
     * @param Query\FacetBuilder[] $facetBuilders
     *
     * @return Facet[]
     */
    protected function buildResultFacets(ezfSearchResultInfo $searchResultInfo, $facetBuilders)
    {
        $facets = [];

        foreach ($facetBuilders as $facetBuilder) {
            $facets[] = $this->facetConverter->buildFacet(
                $facetBuilder,
                $searchResultInfo->attribute('facet_fields'),
                $searchResultInfo->attribute('facet_queries'),
                $searchResultInfo->attribute('facet_dates'),
                $searchResultInfo->attribute('facet_ranges')
            );
        }

        return $facets;
    }
}
