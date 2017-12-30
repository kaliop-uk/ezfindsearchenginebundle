<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Repository;

use Closure;
use Psr\Log\LoggerInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator;
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

    /** @var SortClauseConverter */
    protected $sortClauseConverter;

    protected $defaultBoostFunctions;

    protected $defaultFieldsToReturn;

    protected $defaultReturnObjects;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        Closure $legacyKernelClosure,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        CriteriaConverter $filterCriteriaConverter,
        SortClauseConverter $sortClauseConverter,
        $defaultBoostFunctions,
        $defaultFieldsToReturn,
        $defaultReturnObjects,
        $ezFindModule,
        $ezFindFunction,
        LoggerInterface $logger = null
    ) {
        $this->legacyKernelClosure = $legacyKernelClosure;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->defaultReturnObjects = $defaultReturnObjects;
        $this->ezFindModule = $ezFindModule;
        $this->ezFindFunction = $ezFindFunction;
        $this->logger = $logger;

        // Converters
        $this->filterCriteriaConverter = $filterCriteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;

        // Making sure these are arrays
        $this->defaultBoostFunctions = (array)$defaultBoostFunctions;
        $this->defaultFieldsToReturn = (array)$defaultFieldsToReturn;
    }

    /**
     * @todo fill in the remaining members: time, timedOut, spellSuggestion
     *
     * @param Query $query
     * @param array $fieldFilters
     * @param bool $filterOnUserPermissions
     * @return KaliopSearchResult
     */
    public function findContent(Query $query, array $fieldFilters = [], $filterOnUserPermissions = true)
    {
        $query = clone $query;

        $result = $this->performSearch($query, $fieldFilters, $filterOnUserPermissions);

        $maxScore = null;
        $time = null;

        // q: is there any case where SearchExtras is not set or of a different type?
        if (isset($result['SearchExtras']) && $result['SearchExtras'] instanceof \ezfSearchResultInfo) {
            /** @var \ezfSearchResultInfo $extras */
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

            /// @todo optimize: remove from SearchExtras 'response' to save memory using the 'Closure::bind' hack
        }

        return new KaliopSearchResult(
            [
                //'facets' => $result['Facets'],
                'searchHits' => $result['SearchHits'],
                'time' => $time,
                'maxScore' => $maxScore,
                'totalCount' => $result['SearchCount'],
                'searchExtras' => $result['SearchExtras'],
            ]
        );
    }

    public function findContentInfo(Query $query, array $languageFilter = [], $filterOnUserPermissions = true)
    {
        /// @todo Implement this method shrinking the fieldstoreturn to the bare minimum needed to build contentInfo
        ///       without having to query the database
        throw new NotImplementedException('Intentionally not implemented');
    }

    /// @todo disable asking the total count for speed if possible
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
     * @param Query $query
     * @param array $fieldFilters
     * @param bool $filterOnUserPermissions
     * @param null|bool $forceReturnObjects when set, it overrides both the service default and the query default
     * @return array the same as returned by \eZSolr::Search(), with added members SearchHits and Facets
     */
    protected function performSearch(
        Query $query,
        array $fieldFilters,
        $filterOnUserPermissions,
        $forceReturnObjects = null
    ) {
        $returnObjects = $this->shouldReturnObjects($query, $forceReturnObjects);

        $this->initializeQueryLimit($query);

        $searchParameters = $this->getLegacySearchParameters($query, $fieldFilters, $filterOnUserPermissions, $returnObjects);

        /** @var array $searchResult */
        $searchResult = $this->getLegacyKernel()->runCallback(
            function () use ($searchParameters) {
                return \eZFunctionHandler::execute($this->ezFindModule, $this->ezFindFunction, $searchParameters);
            },
            false
        );

        $this->logSearchErrors($searchResult);

        $searchResult['SearchHits'] = $this->buildResultObjects($searchResult['SearchResult'], $returnObjects);

        //$searchResult['Facets'] = $this->buildResultFacets($searchResult['SearchExtras']);

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
     * @param bool $returnObjects
     * @return array
     */
    protected function getLegacySearchParameters(Query $query, array $fieldFilters, $filterOnUserPermissions, $returnObjects)
    {
        $searchParameters = [
            'offset' => $query->offset,
            'limit' => $query->limit,
            // When we are rebuilding eZ5 objects, no need to load custom fields from Solr.
            // This 'hack' is the way to get ezfind to generate the minimum field list, plus the score
            'fields_to_return' => $returnObjects ? array('meta_score_value:score') : $this->extractLegacyParameter('fields_to_return', $query),
            // we either load eZ5 objects or return solr data, no need to tell ez4 to load objects as well
            'as_objects' => false, //$this->extractLegacyParameter('as_objects', $query),
            'query_handler' => $this->extractLegacyParameter('query_handler', $query),
            'enable_elevation' => $this->extractLegacyParameter('enable_elevation', $query),
            'force_elevation' => $this->extractLegacyParameter('force_elevation', $query),
            'boost_functions' => $this->extractLegacyParameter('boost_functions', $query),
        ];

        if ($query->criterion) {
            //
            $searchParameters['query'] = ''; // seems to work well enough - we put everything in the filter...
            $searchParameters['filter'] = $this->extractFilter($query->criterion);
            //$searchParameters['facet'] = array_merge($this->generateBaseFacets(), $this->extractFacetFilter($criterion));
        }

        if ($query->sortClauses) {
            $searchParameters['sort_by'] = $this->extractSort($query->sortClauses);
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
                return ($query instanceof KaliopQuery) ? $query->enableElevation : true;
            case 'fields_to_return':
                return ($query instanceof KaliopQuery && is_array(
                        $query->fieldsToReturn
                    )) ? $query->fieldsToReturn : $this->defaultFieldsToReturn;
            case 'force_elevation':
                return ($query instanceof KaliopQuery) ? $query->forceElevation : false;
            case 'query_handler':
                return ($query instanceof KaliopQuery) ? $query->queryHandler : 'ezpublish';
        }
    }

    /**
     * Order of importance:
     * 1. override (function parameter)
     * 2. query member
     * 3. default for this service
     * @param Query $query
     * @param null $forceReturnObjects
     * @return bool
     */
    protected function shouldReturnObjects(Query $query, $forceReturnObjects = null)
    {
        if ($forceReturnObjects !== null) {
            return $forceReturnObjects;
        }

        return ($query instanceof KaliopQuery) ? !$query->returnRawData : $this->defaultReturnObjects;
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
            !isset($searchResult['SearchExtras']) || !($searchResult['SearchExtras'] instanceof \ezfSearchResultInfo)
        ) {
            if ($this->logger) {
                $this->logger->error('The legacy search result array misses expected members');
            }

            return;
        }

        /** @var \ezfSearchResultInfo $searchExtras */
        $searchExtras = $searchResult['SearchExtras'];
        $errors = $searchExtras->attribute('error');
        if (!empty($errors) && $this->logger) {
            $this->logger->error(print_r($errors, true));
        }
    }

    /**
     * @param array|null $searchResults
     * @param bool $returnObjects
     * @return SearchHit[]|array depending on $returnObjects
     */
    protected function buildResultObjects($searchResults, $returnObjects)
    {
        if (!is_array($searchResults)) {
            return [];
        }

        if ($returnObjects) {
            foreach ($searchResults as $index => $result) {
                try {
                    $searchResults[$index] = new SearchHit(
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
            }
        }

        return $searchResults;
    }

//    /**
//     * @param array $facetBuilder
//     */
//    public function setFacetBuilder($facetBuilder)
//    {
//        $this->facetBuilder = $facetBuilder;
//    }
//    protected function generateBaseFacets()
//    {
//        $result = [];
//
//        // Add in the base facets
//        foreach ($this->facetBuilder as $builder) {
//            $facetVisitorResult = $this->facetBuilderVisitor->visit($builder);
//            if (!empty($facetVisitorResult)) {
//                $result = array_merge($result, $facetVisitorResult);
//            }
//        }
//
//        return $result;
//    }
//
//    protected function extractFacetFilter(&$criterion)
//    {
//        // Still need to check the logical operators
//        if ($this->criteriaConverter->canHandle($criterion, 'logicalHandlers')) {
//            return $this->extractSubtreeArray($criterion->criteria);
//        }
//
//        $result = [];
//
//        // Process any criterion
////        foreach( $criterion as $index => $filter ) {
////            if( $this->criterionVisitor->canVisit( $filter ) ) {
////                $result[] = [ 'query' => $this->criterionVisitor->visit( $filter ) ];
////                unset( $criterion[$index] );
////            }
////        }
//
//        return $result;
//    }
//
//    protected function buildResultFacets(\ezfSearchResultInfo $searchExtras)
//    {
//        $facets = [];
//
//        if (!isset($this->taxonomyService)) {
//            return $facets;
//        }
//
//        if ($searchExtras->hasAttribute('facet_fields')) {
//            $facetFields = $searchExtras->attribute('facet_fields');
//            if (!is_array($facetFields)) {
//                return $facets;
//            }
//
//            foreach ($facetFields as $facet) {
//                $fieldName = reset($facet['fieldList']);
//                if (empty($fieldName)) {
//                    continue;
//                }
//                $facetField = $this->facetBuilderVisitor->map($fieldName, $facet);
//
//                try {
//                    $taxonomy = $this->taxonomyService->loadTaxonomy($facetField->name);
//                    if (empty($taxonomy)) {
//                        // @todo Something has gone wrong...the facet returned doesn't exist as a taxonomy?
//                        continue;
//                    }
//
//                    $result = $this->buildFacetResult($facetField->entries['countList'],
//                        $taxonomy,
//                        $taxonomy->getRoots());
//
//                    if (!empty($result)) {
//                        $facetKey = $facetField->name;
//                        if (!empty($this->solrFieldFacetMapping[$fieldName])) {
//                            $facetKey = $this->solrFieldFacetMapping[$fieldName];
//                        }
//
//                        $facets[$facetKey] = [
//                            'label' => $facetField->entries['label'],
//                            'facetList' => $result,
//                        ];
//                    }
//                } catch (\Exception $e) {
//                    // @todo Taxonomy wasn't found...log it
//                }
//            }
//        }
//
//        return $facets;
//    }
//
//    protected function buildFacetResult(
//        array $countList,
//        ContentTree $taxonomy,
//        array $locationIdList
//    ) {
//        $result = [];
//        foreach ($locationIdList as $locationId) {
//            $node = $taxonomy->getNodeByLocationId($locationId);
//            if (isset($countList[$node->getId()])) {
//                $count = $countList[$node->getId()];
//            } else {
//                $count = 0;
//            }
//
//            $result[$locationId] = [
//                'content' => $node->getContent(),
//                'isFlat' => $taxonomy->isFlat(),
//                'count' => $count,
//                'children' => [],
//            ];
//
//            $childrenLocationIdList = $node->getChildren();
//            if (!empty($childrenLocationIdList)) {
//                $result[$locationId]['children'] = $this->buildFacetResult($countList,
//                    $taxonomy,
//                    $childrenLocationIdList);
//            }
//
//            // Remove empty facets
//            if ($result[$locationId]['count'] == 0 && empty($result[$locationId]['children'])) {
//                unset($result[$locationId]);
//            }
//        }
//
//        return $result;
//    }
//
//    public function extractAppliedFilters($searchCriterion)
//    {
//        if ($searchCriterion instanceof Query) {
//            return $this->extractAppliedFilters($searchCriterion->criterion);
//        } elseif ($searchCriterion instanceof Query\Criterion\LogicalOperator) {
//            return $this->extractAppliedFilters($searchCriterion->criteria);
//        }
//        $result = [];
//
//        if (!is_array($searchCriterion)) {
//            $searchCriterion = [$searchCriterion];
//        }
//
//        foreach ($searchCriterion as $criterion) {
//            if (!$criterion instanceof Criterion\Field) {
//                continue;
//            }
//
//            list($contentClass, $contentAttribute) = array_pad(explode('/', $criterion->target, 2), -2, null);
//
//            $taxonomy = $this->taxonomyService->loadTaxonomy($contentAttribute);
//            if (empty($taxonomy)) {
//                // @todo Something went wrong...we should do something
//                continue;
//            }
//
//            foreach ($criterion->value as $taxonomyId) {
//                $term = $taxonomy->getNodeByObjectId($taxonomyId);
//                if (empty($term)) {
//                    continue;
//                }
//
//                $parentTerm = $taxonomy->getNodeByLocationId($term->getParentLocationId());
//                if (empty($parentTerm)) {
//                    // @todo - Parent wasn't found...what should we do?
//                    continue;
//                }
//
//                if (empty($result[$criterion->target][$parentTerm->getId()])) {
//                    $result[$criterion->target][$parentTerm->getId()] = [
//                        'term' => $parentTerm,
//                        'children' => [],
//                    ];
//                }
//
//                $result[$criterion->target][$parentTerm->getId()]['children'][$term->getId()] = $term;
//            }
//        }
//
//        return $result;
//    }

}
