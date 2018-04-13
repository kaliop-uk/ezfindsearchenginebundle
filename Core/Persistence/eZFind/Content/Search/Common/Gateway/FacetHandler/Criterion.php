<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\CriterionFacet;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

class Criterion extends FacetHandler
{
    /**
     * @var CriteriaConverter
     */
    protected $criteriaConverter;

    /**
     * Criterion constructor.
     *
     * @param CriteriaConverter $criteriaConverter
     */
    public function __construct(CriteriaConverter $criteriaConverter)
    {
        $this->criteriaConverter = $criteriaConverter;
    }

    /**
     * @inheritdoc
     */
    public function accept(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FacetBuilder\CriterionFacetBuilder;
    }

    /**
     * @inheritdoc
     *
     * @param FacetBuilder\CriterionFacetBuilder $facetBuilder
     */
    public function handle(FacetBuilder $facetBuilder)
    {
        $facet = $this->buildFacet($facetBuilder);
        $facet['query'] = $this->criteriaConverter->handle($facetBuilder->filter);

        return $facet;
    }

    /**
     * @inheritdoc
     *
     * @param FacetBuilder\FieldFacetBuilder $facetBuilder
     */
    public function createFacetResult(FacetBuilder $facetBuilder, $fields = [], $queries = [], $dates = [], $ranges = [])
    {
        $facetKey = $this->getFacetKey($facetBuilder);
        $facetQuery = $this->criteriaConverter->handle($facetBuilder->filter);

        $count = 0;
        foreach ($queries as $query) {
            if (
                (isset($query['facet_key']) && $query['facet_key'] == $facetKey) ||
                (!isset($query['facet_key']) && $query['queryLimit'] == $facetQuery)
            ) {
                $count = $query['count'];

                break;
            }
        }

        return new CriterionFacet([
            'name' => $facetBuilder->name,
            'count' => $count,
        ]);
    }
}