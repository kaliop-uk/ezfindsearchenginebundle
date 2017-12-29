<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Search;

use eZ\Publish\API\Repository\Values\Content\Search\SearchHit as BaseSearchHit;

/**
 * @property-read bool $elevated
 */
class SearchHit extends BaseSearchHit
{
    protected $elevated;
}
