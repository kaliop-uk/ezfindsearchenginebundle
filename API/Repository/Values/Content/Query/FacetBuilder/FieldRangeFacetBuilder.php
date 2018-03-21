<?php

namespace Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\FacetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Build a field range facet.
 *
 * If provided the search service returns a FieldRangeFacet for the given field path.
 * A field path starts with a field identifier and may contain a subpath in the case
 * of complex field types
 */
class FieldRangeFacetBuilder extends FacetBuilder
{
    const OTHER_BEFORE = 'before';
    const OTHER_AFTER = 'after';
    const OTHER_BETWEEN = 'between';
    const OTHER_NONE = 'none';
    const OTHER_ALL = 'all';

    const INCLUDE_LOWER = 'lower';
    const INCLUDE_UPPER = 'upper';
    const INCLUDE_EDGE = 'edge';
    const INCLUDE_OUTER = 'outer';
    const INCLUDE_ALL = 'all';

    /**
     * The field paths starts with a field identifier and a sub path (for complex types).
     *
     * @var string
     */
    public $fieldPath;

    /**
     * Range start: any value valid for the numeric/data type.
     *
     * @var string
     */
    public $start;

    /**
     * Range end: any value valid for the numeric/data type.
     *
     * @var string
     */
    public $end;

    /**
     * Specifies the span of the range as a value to be added to the lower bound.
     *
     * @var string
     */
    public $gap;

    /**
     * Instructing Solr what to do if the intervals do not divide nicely between start and end.
     * If true, the 'end' parameter will be enforced. Otherwise, the gap will be used to determine the real end value.
     *
     * @var bool
     */
    public $hardend = false;

    /**
     * Will be used to tell the back end to provide more, less, all or no counts other than the ones in specified range.
     *
     * @var string
     */
    public $other = self::OTHER_NONE;

    /**
     * Specifies inclusion and exclusion preferences for the upper and lower bounds of the range.
     *
     * @var string
     */
    public $include = self::INCLUDE_LOWER;
}