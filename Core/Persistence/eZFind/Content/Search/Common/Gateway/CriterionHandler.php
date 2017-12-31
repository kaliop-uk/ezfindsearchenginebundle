<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

abstract class CriterionHandler implements Handler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    abstract public function accept(Criterion $criterion);

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @param CriteriaConverter $converter
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     */
    abstract public function handle(CriteriaConverter $converter, Criterion $criterion);

    /**
     * @param string $value
     *
     * @return string
     *
     * @todo if we get passed a string such as 'xxx AND hello', we should probbaly add double quotes around it, as we
     *       will otherwise most likely be attempting a match for the world 'hello' on any field, not just one
     */
    protected function escapeValue($value)
    {
        return str_replace(
            array('\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', '/'),
            array('\\\\', '\\+', '\\-', '\\&&', '\\||', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\"', '\\~', '\\*', '\\?', '\\:', '\\/'),
            $value
        );
    }

    /**
     * @param int $value timestamp
     */
    protected function formatDate($value)
    {
        return strftime('%Y-%m-%dT%H:%M:%SZ', $value);
    }

    protected function formatDateByRef(&$value)
    {
        $value = strftime('%Y-%m-%dT%H:%M:%SZ', $value);
    }
}
