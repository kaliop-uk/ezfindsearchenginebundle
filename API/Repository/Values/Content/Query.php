<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Query as BaseQuery;

class Query extends BaseQuery
{
    const RETURN_CONTENTS = 'contents';
    const RETURN_EZFIND_DATA = 'ezfind_data';
    const RETURN_SOLR_DATA = 'solr_data';

    /**
     * @var string $returnRawData set this to 'ezfind_data' or 'solr_data' have the eZFind data returned instead of
     *                            Contents for the SearchHit->valueObject. This means no db lookups.
     *                            When left NULL, it is up to the
     */
    public $returnType = null;

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
