<?php


namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriteriaConverter;
use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;
use Kaliop\EzFindSearchEngineBundle\Core\Base\Exceptions\NotImplementedException;

class UserMetadata extends CriterionHandler
{
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\UserMetadata;
    }

    public function handle(CriteriaConverter $converter, Criterion $criterion)
    {
        switch ($criterion->target)
        {
            case Criterion\UserMetadata::OWNER:
                $valueList = (array)$criterion->value;

                foreach ($valueList as $value) {
                    $result[] = 'owner_id:' . $this->escapeValue($value);
                }

                return count($result) == 1 ? $result[0] : array_unshift($result, 'OR');

            case Criterion\UserMetadata::GROUP:
                $valueList = (array)$criterion->value;

                foreach ($valueList as $value) {
                    $result[] = 'owner_group_id:' . $this->escapeValue($value);
                }

                return count($result) == 1 ? $result[0] : array_unshift($result, 'OR');

            default:
                throw new NotImplementedException("Can not convert UserMetadata criterion for target {$criterion->target}: no such data in the solr schema by default");
        }
    }
}
