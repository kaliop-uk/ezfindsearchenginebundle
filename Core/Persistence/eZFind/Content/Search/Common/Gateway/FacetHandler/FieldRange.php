<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\FieldRangeFacet;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\RangeFacetEntry;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\FacetBuilder\FieldRangeFacetBuilder;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

class FieldRange extends FacetHandler
{
    /**
     * @inheritdoc
     */
    public function accept(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FieldRangeFacetBuilder;
    }

    /**
     * @inheritdoc
     *
     * @param FieldRangeFacetBuilder $facetBuilder
     */
    public function handle(FacetBuilder $facetBuilder)
    {
        $facet = $this->buildFacet($facetBuilder);
        $facet['range'] = [
            'field' => $facetBuilder->fieldPath,
            'start' => $facetBuilder->start,
            'end' => $facetBuilder->end,
            'gap' => $facetBuilder->gap,
            'hardend' => $facetBuilder->hardend,
            'include' => $facetBuilder->include,
            'other' => $facetBuilder->other,
        ];

        return $facet;
    }

    /**
     * @inheritdoc
     *
     * @param FieldRangeFacetBuilder $facetBuilder
     */
    public function createFacetResult(FacetBuilder $facetBuilder, $fields = [], $queries = [], $dates = [], $ranges = [])
    {
        $entries = [];
        $totalCount = 0;

        $facetKey = $this->getFacetKey($facetBuilder);
        if (isset($ranges[$facetKey])) {
            foreach ($ranges[$facetKey]['counts'] as $value => $count) {
                $facetEntry = new RangeFacetEntry();
                $facetEntry->from = $value;
                $facetEntry->to = $value;
                $facetEntry->totalCount = $count;

                $entries[] = $facetEntry;
                $totalCount += $count;
            }
        }

        return new FieldRangeFacet([
            'name' => $facetBuilder->name,
            'entries' => $entries,
            'totalCount' => $totalCount,
        ]);
    }
}