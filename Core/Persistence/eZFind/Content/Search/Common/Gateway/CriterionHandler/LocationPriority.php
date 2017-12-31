<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

class LocationPriority extends CriterionHandler
{
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\LocationPriority;
    }

    public function handle( CriteriaConverter $converter, Criterion $criterion )
    {
        switch ( $criterion->operator )
        {
            case Criterion\Operator::BETWEEN:
                return 'priority:[' . $this->escapeValue($criterion->value[0]) . ' TO ' . $this->escapeValue($criterion->value[1]) . ']';

            case Criterion\Operator::GT:
                return 'priority:{' . $this->escapeValue(reset( $criterion->value )) . ' TO *]';

            case Criterion\Operator::GTE:
                return 'priority:[' . $this->escapeValue(reset( $criterion->value )) . ' TO *]';

            case Criterion\Operator::LT:
                return 'priority:[* TO ' . $this->escapeValue(reset( $criterion->value )) . '}';

            case Criterion\Operator::LTE:
                return 'priority:[* TO ' . $this->escapeValue(reset( $criterion->value )) . ']';

        }
    }
}
