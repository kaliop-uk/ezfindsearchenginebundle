<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Query as BaseQuery;

class Query extends BaseQuery
{
    /**
     * @var bool $returnRawData when true, the eZFind data will be returned as valueObjects instead of Contents.
     *                          This means no db lookups as well
     */
    public $returnRawData = false;

    /**
     * @var array $fieldsToReturn when $returnRawData is true, you can use this to customize the list of fields to be returned.
     *                            If not set, the search service will inject its own list of fields most likely... (?)
     */
    public $fieldsToReturn = null;

    /**
     * @var array $boostFunctions you can use this to customize the boost functions.
     *                            If not set, the search service will inject its own boost functions most likely... (?)
     */
    public $boostFunctions = null;

    public $enableElevation = true;

    public $forceElevation = false;

    public $queryHandler = 'ezpublish';
}
