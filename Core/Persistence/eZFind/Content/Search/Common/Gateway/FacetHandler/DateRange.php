<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\DateRangeFacet;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\RangeFacetEntry;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\FacetBuilder\DateRangeFacetBuilder;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;
use DateInterval;
use DateTime;

class DateRange extends FacetHandler
{
    // SOLR does not support TimeZones for DateTimes
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
     * @inheritdoc
     */
    public function accept(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof DateRangeFacetBuilder;
    }

    /**
     * @inheritdoc
     *
     * @param DateRangeFacetBuilder $facetBuilder
     */
    public function handle(FacetBuilder $facetBuilder)
    {
        $facet = $this->buildFacet($facetBuilder);
        $facet['range'] = [
            'field' => $facetBuilder->fieldPath,
            'start' => $facetBuilder->start->format(self::DATE_FORMAT),
            'end' => $facetBuilder->end->format(self::DATE_FORMAT),
            'gap' => $this->convertDateInterval($facetBuilder->gap),
            'hardend' => $facetBuilder->hardend,
            'include' => $facetBuilder->include,
            'other' => $facetBuilder->other,
        ];

        return $facet;
    }

    /**
     * @inheritdoc
     *
     * @param DateRangeFacetBuilder $facetBuilder
     */
    public function createFacetResult(FacetBuilder $facetBuilder, $fields = [], $queries = [], $dates = [], $ranges = [])
    {
        $facetKey = $this->getFacetKey($facetBuilder);
        $fieldName = \eZSolr::getFieldName($facetBuilder->fieldPath, false, 'facet');

        $entries = [];
        foreach ($ranges as $field => $range) {
            if (in_array($field, [$facetKey, $fieldName])) {
                foreach ($range['counts'] as $date => $count) {
                    $from = new DateTime($date);
                    $to = clone $from;
                    $to->add($facetBuilder->gap);

                    $facetEntry = new RangeFacetEntry();
                    $facetEntry->from = $from;
                    $facetEntry->to = $to;
                    $facetEntry->totalCount = $count;

                    $entries[] = $facetEntry;
                }

                break;
            }
        }

        return new DateRangeFacet([
            'name' => $facetBuilder->name,
            'entries' => $entries,
        ]);
    }

    /**
     * Convert PHP DateInterval into JAVA DateMathParser syntax.
     *
     * @param DateInterval $dateInterval
     * @return string
     */
    protected function convertDateInterval(DateInterval $dateInterval)
    {
        $sign = $dateInterval->invert? '-' : '+';
        if ($dateInterval->days) {
            return $sign . $dateInterval->days . 'DAY' . (($dateInterval->y > 1)? 'S' : '');
        }

        $syntax = '';
        if ($dateInterval->y) {
            $syntax .= $sign . $dateInterval->y . 'YEAR' . (($dateInterval->y > 1)? 'S' : '');
        }
        if ($dateInterval->m) {
            $syntax .= $sign . $dateInterval->m . 'MONTH' . (($dateInterval->m > 1)? 'S' : '');
        }
        if ($dateInterval->d) {
            $syntax .= $sign . $dateInterval->d . 'DAY' . (($dateInterval->d > 1)? 'S' : '');
        }
        if ($dateInterval->h) {
            $syntax .= $sign . $dateInterval->h . 'HOUR' . (($dateInterval->h > 1)? 'S' : '');
        }
        if ($dateInterval->i) {
            $syntax .= $sign . $dateInterval->i . 'MINUTE' . (($dateInterval->i > 1)? 'S' : '');
        }
        if ($dateInterval->s) {
            $syntax .= $sign . $dateInterval->s . 'SECOND' . (($dateInterval->s > 1)? 'S' : '');
        }

        return $syntax;
    }
}