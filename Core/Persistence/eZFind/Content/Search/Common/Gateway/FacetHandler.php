<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;
use Closure;
use ezpKernel;

abstract class FacetHandler implements Handler
{
    /**
     * @var Closure
     */
    private $legacyKernel;

    /**
     * @param Closure $legacyKernel
     */
    public function setLegacyKernel(Closure $legacyKernel)
    {
        $this->legacyKernel = $legacyKernel;
    }

    /**
     * Check if this facet handler accepts to handle the given facet builder.
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return bool
     */
    abstract public function accept(FacetBuilder $facetBuilder);

    /**
     * Map facet builder value to a proper SOLR representation.
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return array
     */
    abstract public function handle(FacetBuilder $facetBuilder);

    /**
     * Create new facet value based on eZFind facet results.
     *
     * @param FacetBuilder $facetBuilder
     * @param array $fields
     * @param array $queries
     * @param array $dates
     * @param array $ranges
     *
     * @return Facet
     */
    abstract public function createFacetResult(FacetBuilder $facetBuilder, $fields = [], $queries = [], $dates = [], $ranges = []);

    /**
     * Get eZ Legacy kernel.
     *
     * @return ezpKernel
     */
    protected function getLegacyKernel()
    {
        $legacyKernelClosure = $this->legacyKernel;
        return $legacyKernelClosure();
    }

    /**
     * Build base facet based on FacetBuilder parent class.
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return array
     */
    protected function buildFacet(FacetBuilder $facetBuilder)
    {
        $facet = [
            'mincount' => $facetBuilder->minCount,
            'limit' => $facetBuilder->limit,
            'facet_key' => true,
        ];

        return $facet;
    }

    /**
     * Get SOLR field name based on a name which may either be a
     * meta-data name, or an eZ Publish content class attribute, specified by
     * <class identifier>/<attribute identifier>[/<option>]
     *
     * @param string $baseName
     *
     * @return string
     */
    protected function getSolrFieldName($baseName)
    {
        return $this->getLegacyKernel()->runCallback(
            function () use ($baseName) {
                return \eZSolr::getFieldName($baseName, false, 'facet');
            },
            false
        );
    }

    /**
     * Get facet key returned by SOLR.
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return string
     */
    protected function getFacetKey(FacetBuilder $facetBuilder)
    {
        return md5(json_encode($this->handle($facetBuilder)));
    }
}
