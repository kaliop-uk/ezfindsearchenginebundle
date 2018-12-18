<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion as BaseCriterion;

/**
 * Full-text search criterion, based on eZFind configuration.
 * When this criterion is used (NB: as part of a query's `query`, not `filter`), the given text will be matched against
 * all content fields indexed in Solr as per eZFind configuration (including field boosts etc...)
 * Note: when using the FullTextr criterion instead of this one, matching is done against the `ezf_df_text` Solr field
 */
class EzFindText extends BaseCriterion
{
    public function __construct($value)
    {
        $this->value = $value;
    }
}
