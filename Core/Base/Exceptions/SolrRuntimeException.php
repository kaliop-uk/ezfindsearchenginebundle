<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Base\Exceptions;

use RuntimeException;

class SolrRuntimeException extends RuntimeException
{
    /**
     * SolrRuntimeException constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct($message, $code = 400)
    {
        parent::__construct(
            sprintf('Wrong HTTP status received from Solr: %s', $message),
            $code
        );
    }
}