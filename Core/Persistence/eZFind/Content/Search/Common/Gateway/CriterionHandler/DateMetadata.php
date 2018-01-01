<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class DateMetadata extends CriterionHandler
{
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\DateMetadata;
    }

    public function handle( CriteriaConverter $converter, Criterion $criterion )
    {
        $field = $criterion->target == Criterion\DateMetadata::MODIFIED ? 'modified' : 'published';

        switch ( $criterion->operator )
        {
            case Criterion\Operator::BETWEEN:
                return $field . ':[' . $this->formatDate($criterion->value[0]) . ' TO ' . $this->formatDate($criterion->value[1]) . ']';

            case Criterion\Operator::GT:
                return $field . ':{' . $this->formatDate(reset($criterion->value)) . ' TO *]';

            case Criterion\Operator::GTE:
                return $field . ':[' . $this->formatDate(reset($criterion->value)) . ' TO *]';

            case Criterion\Operator::LT:
                return $field . ':[* TO ' . $this->formatDate(reset($criterion->value)) . '}';

            case Criterion\Operator::LTE:
                return $field . ':[* TO ' . $this->formatDate(reset($criterion->value)) . ']';

            /// @todo this currently breaks solr
            case Criterion\Operator::IN:
                $values = array_map(array($this, 'formatDate'), $criterion->value);
                return $field . ':(' . implode(' ', $values) . ')';
        }
    }
}
