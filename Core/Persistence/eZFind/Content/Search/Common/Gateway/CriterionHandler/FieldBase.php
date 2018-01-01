<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

abstract class FieldBase extends CriterionHandler
{
    /** @var \Closure */
    protected $legacyKernelClosure;

    public function __construct(\Closure $legacyKernelClosure)
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
    }

    /**
     * @return \ezpKernel
     */
    protected function getLegacyKernel()
    {
        $legacyKernelClosure = $this->legacyKernelClosure;

        return $legacyKernelClosure();
    }

    /// @todo (!important) verify: do we really need to use a runCallback call here ?
    protected function determineFieldName($fieldName)
    {
        $fieldName = $this->getLegacyKernel()->runCallback(
            function () use ($fieldName) {
                return \eZSolr::getFieldName($fieldName, false, 'facet');
            },
            false
        );

        return $fieldName;
    }
}
