<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\DateRangeFacet;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\RangeFacetEntry;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\FacetBuilder\DateRangeFacetBuilder;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\FacetHandler;
use DateInterval;

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
        $entries = [];

        $facetKey = $this->getFacetKey($facetBuilder);
        if (isset($ranges[$facetKey])) {
            foreach ($ranges[$facetKey]['counts'] as $date => $count) {
                $facetEntry = new RangeFacetEntry();
                $facetEntry->from = new \DateTime($date);
                $facetEntry->to = new \DateTime($date);
                $facetEntry->totalCount = $count;

                $entries[] = $facetEntry;
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