<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\ContentTypeFacet;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

class ContentType extends FacetHandler
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * ContentType constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function accept(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FacetBuilder\ContentTypeFacetBuilder;
    }

    /**
     * @inheritdoc
     */
    public function handle(FacetBuilder $facetBuilder)
    {
        $facet = $this->buildFacet($facetBuilder);
        $facet['field'] = 'class';

        return $facet;
    }

    /**
     * @inheritdoc
     */
    public function createFacetResult(FacetBuilder $facetBuilder, $fields = [], $queries = [], $dates = [], $ranges = [])
    {
        $facetKey = $this->getFacetKey($facetBuilder);

        $entries = [];
        foreach ($fields as $field) {
            if (
                (isset($field['facet_key']) && $field['facet_key'] == $facetKey) ||
                (!isset($field['facet_key']) && $field['field'] == 'class')
            ) {
                foreach ($field['countList'] as $contentTypeId => $count) {
                    $contentType = $this->repository->getContentTypeService()->loadContentType($contentTypeId);
                    $entries[$contentType->identifier] = $count;
                }
                
                break;
            }
        }

        return new ContentTypeFacet([
            'name' => $facetBuilder->name,
            'entries' => $entries,
        ]);
    }
}