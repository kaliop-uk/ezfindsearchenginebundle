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
        // Search for all
        if (trim($criterion->value) == '*') {
            return 'ezf_df_text:*';
        }

        $value = $this->escapeValue(trim($criterion->value));

        // Check if wildcard query
        if ($value && $value != '*' && trim($value, '*') != $value) {
            // Escape spaces
            $value = str_replace(' ', '\\ ', $value);

            // Un-escape wildcard
            $wildcard = str_replace('\\*', '*', $value);

            // Non-wildcard value
            $value = trim($value, '\*');

            $value = $value . '^2 OR ' . $wildcard;
        }

        return 'ezf_df_text:(' . $value . ')';
    }
}
