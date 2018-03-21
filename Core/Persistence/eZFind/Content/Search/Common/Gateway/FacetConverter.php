<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use Kaliop\EzFindSearchEngineBundle\Core\Base\Exceptions\NotImplementedException;

class FacetConverter extends Converter
{
    /**
     * Check if the FacetBuilder can be handled by any of the available handlers.
     *
     * @param FacetBuilder $facetBuilder
     * @return bool
     */
    public function canHandle(FacetBuilder $facetBuilder)
    {
        /** @var FacetHandler $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->accept($facetBuilder)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Map facet builder value to a proper SOLR representation.
     *
     * @param FacetBuilder $facetBuilder
     * @return array
     *
     * @throws NotImplementedException
     */
    public function handle(FacetBuilder $facetBuilder)
    {
        /** @var FacetHandler $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->accept($facetBuilder)) {
                return $handler->handle($facetBuilder);
            }
        }

        throw new NotImplementedException(
            'No facet handler available for: ' . get_class($facetBuilder)
        );
    }

    /**
     * Map eZFind facet results to correct facet handler.
     *
     * @param FacetBuilder $facetBuilder
     * @param array $fields
     * @param array $queries
     * @param array $dates
     * @param array $ranges
     * @return \eZ\Publish\API\Repository\Values\Content\Search\Facet
     *
     * @throws NotImplementedException
     */
    public function buildFacet(FacetBuilder $facetBuilder, $fields = [], $queries = [], $dates = [], $ranges = [])
    {
        foreach ($this->handlers as $handler) {
            /** @var FacetHandler $handler */
            if ($handler->accept($facetBuilder)) {
                return $handler->createFacetResult($facetBuilder, $fields, $queries, $dates, $ranges);
            }
        }

        throw new NotImplementedException(
            'No facet handler available for: ' . get_class($facetBuilder)
        );
    }
}
