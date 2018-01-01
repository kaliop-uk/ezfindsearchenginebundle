<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class Field extends FieldBase
{
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\Field;
    }

    /// @todo should we apply specific conversions based on field type?
    public function handle( CriteriaConverter $converter, Criterion $criterion )
    {
        $field = $this->determineFieldName($criterion->target);

        switch ( $criterion->operator )
        {
            case Criterion\Operator::IN:
                $values = array_map(array($this, 'escapeValue'), $criterion->value);
                return $field . ':(' . implode(' ', $values) . ')';

            case Criterion\Operator::GT:
                return $field . ':{' . $this->escapeValue($criterion->value) . ' TO *]';

            case Criterion\Operator::GTE:
                return $field . ':[' . $this->escapeValue($criterion->value) . ' TO *]';

            case Criterion\Operator::LT:
                return $field . ':[* TO ' . $this->escapeValue($criterion->value) . '}';

            case Criterion\Operator::LTE:
                return $field . ':[* TO ' . $this->escapeValue($criterion->value) . ']';

            case Criterion\Operator::LIKE:
                return $field . ':' . $this->like2regexp($this->escapeValue($criterion->value));

            case Criterion\Operator::BETWEEN:
                return $field . ':[' . $this->escapeValue($criterion->value[0]) . ' TO ' . $this->escapeValue($criterion->value[1]) . ']';

            case Criterion\Operator::CONTAINS:
                return $field . ':*' . $this->escapeValue($criterion->value) . '*';
        }
    }

    /**
     * Transform db-style LIKE in regexp
     * - replace %, _ chars
     * - escape regexp chars
     */
    protected function like2regexp($string, $delimiter='/')
    {
        $string = preg_quote($string, $delimiter);
        $string = str_replace(array('%', '_'), array('.*', '.'), $string);
        return $delimiter . $string . $delimiter;
    }
}
