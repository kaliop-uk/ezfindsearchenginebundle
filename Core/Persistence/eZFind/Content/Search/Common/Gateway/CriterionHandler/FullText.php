<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class FullText extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\FullText;
    }

    /**
     * @todo check if we do respect the matching logic as described in class Criterion\Field:
     *       - do NOT escape wildcards
     *       - default to AND instead of OR for separate words
     */
    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        // Fulltext only accepts 1 value
        return 'ezf_df_text:'.$this->escapeValue($criterion->value);
    }
}
