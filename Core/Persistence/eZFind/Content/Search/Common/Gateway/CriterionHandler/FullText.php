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
        $value = trim($criterion->value);

        if ($value == '*') {
            // Pure wildcard query
            return 'ezf_df_text:*';

        } else if (preg_match('/^".+"$/', $value)) {
            // Quoted-string query: escape everything but the outher quotes
            $value = '"' . $this->escapeValue(substr($value, 1, -1)) . '"';

        } else if (preg_match('/(^\*|\*$)/', $value)) {
            // Wildcard query: make the exacth match stronger than the wildcard

            // @bug we do not support wildcard chars in the middle of phrases

            $value = $this->escapeValue($value);

            // Escape spaces
            $value = str_replace(' ', '\\ ', $value);

            // wildcard match: un-escape wildcard char
            $wildcard = str_replace('\\*', '*', $value);

            // Non-wildcard match
            $value = trim($value, '*');
            $value = rtrim($value, '\\');

            $value = $value . '^2 OR ' . $wildcard;

        } else {
            // plain query
            $value = $this->escapeValue($value);
        }

        return 'ezf_df_text:(' . $value . ')';
    }
}
