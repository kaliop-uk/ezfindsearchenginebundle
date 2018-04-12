<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\FieldFacet;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

class Field extends FacetHandler
{
    /**
     * @inheritdoc
     */
    public function accept(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FacetBuilder\FieldFacetBuilder;
    }

    /**
     * @inheritdoc
     *
     * @param FacetBuilder\FieldFacetBuilder $facetBuilder
     */
    public function handle(FacetBuilder $facetBuilder)
    {
        $facet = $this->buildFacet($facetBuilder);
        $facet['field'] = $facetBuilder->fieldPaths;

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
        $fieldNames = [];
        foreach ((array) $facetBuilder->fieldPaths as $fieldPath) {
            $fieldNames[] = \eZSolr::getFieldName($fieldPath, false, 'facet');
        }

        $entries = [];
        $total = 0;

        foreach ($fields as $field) {
            if (
                (isset($field['facet_key']) && $field['facet_key'] == $facetKey) ||
                (!isset($field['facet_key']) && in_array($field['field'], $fieldNames))
            ) {
                foreach ($field['countList'] as $word => $count) {
                    $entries[$word] = $count;
                }

                foreach ($entries as $word => $count) {
                    $total += intval($count);
                }

                if (isset($field['facet_key'])) {
                    break;
                }
            }
        }

        return new FieldFacet([
            'name' => $facetBuilder->name,
            'entries' => $entries,
            'totalCount' => $total,
        ]);
    }
}