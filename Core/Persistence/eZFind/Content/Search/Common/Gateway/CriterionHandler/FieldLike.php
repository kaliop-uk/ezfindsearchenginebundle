<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Persistence\Solr\Content\Search\CriterionVisitor;
use Kaliop\EzFindSearchEngineBundle\Persistence\Solr\Content\Search\CriterionVisitorDispatcher;

/// @deprecated
class FieldLike extends FieldBase
{
    // don't allow if is not a text field.
    public function accept(Criterion $criterion)
    {
        $solrFieldName = \eZSolr::getFieldName($criterion->target);

        return
            ($criterion instanceof Criterion\Field) &&
            ($criterion->operator === Criterion\Operator::LIKE) &&
            (substr($solrFieldName, -1) === 't');
    }

    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        $fieldName = $this->determineFieldName($criterion->target);
        return "$fieldName:" . $criterion->value;
    }
}
