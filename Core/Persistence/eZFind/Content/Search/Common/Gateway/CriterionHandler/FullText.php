<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class FullText extends CriterionHandler
{
    /**
     * @inheritdoc
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\FullText;
    }

    /**
     * If the FullText search contain wildcard search, build correct wildcard query
     * with non-truncated words boosted.
     *
     * @inheritdoc
     */
    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        // Fulltext only accepts 1 value
        $value = $this->escapeValue(trim($criterion->value));

        // Escape spaces
        $value = str_replace(' ', '\\ ', $value);

        if ($value && $value != '*' && trim($value, '*') != $value) {
            // Wildcard query
            $value = trim($value, '\*');
            $value = $value . '^2 OR ' . $value . '*';
        }

        return 'ezf_df_text:(' . $value . ')';
    }
}
