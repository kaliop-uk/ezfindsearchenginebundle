<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\Persistence\eZFind\Content\Search\Common\Gateway;

abstract class Converter
{
    /**
     * @var Handler[]
     */
    protected $handlers;

    /**
     * Construct from an optional array of handlers.
     *
     * @param Handler[] $handlers
     */
    public function __construct(array $handlers = array())
    {
        $this->handlers = $handlers;
    }

    /**
     * Adds handler.
     *
     * @param Handler $handler
     */
    public function addHandler(Handler $handler)
    {
        $this->handlers[] = $handler;
    }
}
