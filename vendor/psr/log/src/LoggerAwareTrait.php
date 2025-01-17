<?php

namespace ECSPrefix20220403\Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface|null
     */
    protected $logger;
    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(\ECSPrefix20220403\Psr\Log\LoggerInterface $logger) : void
    {
        $this->logger = $logger;
    }
}
