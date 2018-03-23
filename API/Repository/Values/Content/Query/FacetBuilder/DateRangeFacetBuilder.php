<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\FacetBuilder;

use DateTime;

class DateRangeFacetBuilder extends FieldRangeFacetBuilder
{
    /**
     * Range start: any value valid for the numeric/data type.
     *
     * @var DateTime
     */
    public $start;

    /**
     * Range end: any value valid for the numeric/data type.
     *
     * @var DateTime
     */
    public $end;

    /**
     * Specifies the span of the range as a value to be added to the lower bound.
     *
     * @var DateTime
     */
    public $gap;
}