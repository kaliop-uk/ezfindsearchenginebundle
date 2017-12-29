<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

use Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway\CriterionHandler;

abstract class FieldBase extends CriterionHandler
{
    /** @var \Closure */
    protected $legacyKernelClosure;

    public function __construct(\Closure $legacyKernelClosure)
    {
        // Override the base constructor
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

    /// @todo refactor this
    protected function determineFieldName($fieldName)
    {
        $fieldDefinition = explode('/', $fieldName);
        switch (count($fieldDefinition)) {
            case 1:
                return $fieldName;
            case 2:
                // If the field is a taxonomy field, we need to query the id directly
                // The only way to accomplish this is to force the correct fieldname, which we can only get
                // when generating a facet field (which makes sense, but we're also filtering against facets).
                // Why doesn't ezfind handle this better?
                $contentClassAttributeId = \eZContentObjectTreeNode::classAttributeIDByIdentifier($fieldName);
                $contentClassAttribute = \eZContentClassAttribute::fetch($contentClassAttributeId);
                if ($contentClassAttribute->attribute('data_type_string') == 'eztaxonomy') {
                    $fieldName = $this->getLegacyKernel()->runCallback(
                        function () use ($fieldName) {
                            return \eZSolr::getFieldName($fieldName, false, 'facet');
                        },
                        false
                    );
                }
        }

        return $fieldName;
    }
}
