<?php

namespace Kaliop\EzFindSearchEngineBundle\Core\MVC\Legacy\Kernel;

use eZ\Publish\Core\MVC\Legacy\Kernel\Loader as BaseLoader;
use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PostBuildKernelEvent;
use Symfony\Component\HttpFoundation\ParameterBag;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use eZ\Publish\Core\MVC\Legacy\Kernel\CLIHandler;

/**
 * Implement a fix for https://jira.ez.no/browse/EZP-26895 as well as https://jira.ez.no/browse/EZP-26451
 */
class Loader extends BaseLoader
{
    protected $isCli = false;

    public function setCLiMode($isCli=true)
    {
        $this->isCli = $isCli;
    }

    public function buildLegacyKernel($legacyKernelHandlerWeb, $legacyKernelHandlerCli=null)
    {
        $legacyRootDir = $this->legacyRootDir;
        $webrootDir = $this->webrootDir;
        $eventDispatcher = $this->eventDispatcher;
        $logger = $this->logger;
        $that = $this;

        return function () use ($legacyKernelHandlerWeb, $legacyKernelHandlerCli, $legacyRootDir, $webrootDir, $eventDispatcher, $logger, $that) {
            if (LegacyKernel::hasInstance()) {
                return LegacyKernel::instance();
            }

            if ($this->isCli) {
                $legacyKernelHandler = $legacyKernelHandlerCli;
            } else {
                $legacyKernelHandler = $legacyKernelHandlerWeb;
            }

            if ($legacyKernelHandler instanceof \Closure) {
                $legacyKernelHandler = $legacyKernelHandler();
            }
            $legacyKernel = new LegacyKernel($legacyKernelHandler, $legacyRootDir, $webrootDir, $logger);

            if ($that->getBuildEventsEnabled()) {
                $eventDispatcher->dispatch(
                    LegacyEvents::POST_BUILD_LEGACY_KERNEL,
                    new PostBuildKernelEvent($legacyKernel, $legacyKernelHandler)
                );
            }

            return $legacyKernel;
        };
    }

    public function buildLegacyKernelHandlerCLI()
    {
        $legacyRootDir = $this->legacyRootDir;
        $eventDispatcher = $this->eventDispatcher;
        $container = $this->container;
        $that = $this;

        return function () use ($legacyRootDir, $container, $eventDispatcher, $that) {
            if (!$that->getCLIHandler()) {
                $currentDir = getcwd();
                chdir($legacyRootDir);

                $legacyParameters = new ParameterBag($container->getParameter('ezpublish_legacy.kernel_handler.cli.options'));
                if ($that->getBuildEventsEnabled()) {
                    $eventDispatcher->dispatch(LegacyEvents::PRE_BUILD_LEGACY_KERNEL, new PreBuildKernelEvent($legacyParameters));
                }

                $that->setCLIHandler(
                    new CLIHandler($legacyParameters->all(), $container->get('ezpublish.siteaccess'), $container)
                );

                chdir($currentDir);
            }

            return $that->getCLIHandler();
        };
    }
}
