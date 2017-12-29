<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Search;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult as eZSearchResult;

/**
 * @property-read \ezfSearchResultInfo searchExtras
 */
class SearchResult extends eZSearchResult
{
    /** @var \ezfSearchResultInfo */
    protected $searchExtras;
}
