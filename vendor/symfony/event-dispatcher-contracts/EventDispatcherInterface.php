<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210507\Symfony\Contracts\EventDispatcher;

use ECSPrefix20210507\Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
/**
 * Allows providing hooks on domain-specific lifecycles by dispatching events.
 */
interface EventDispatcherInterface extends \ECSPrefix20210507\Psr\EventDispatcher\EventDispatcherInterface
{
    /**
    * Dispatches an event to all registered listeners.
    *
    * @param object      $event     The event to pass to the event handlers/listeners
     * @param string $eventName The name of the event to dispatch. If not supplied,
                             the class of $event should be used instead.
    *
    * @return object The passed $event MUST be returned
    */
    public function dispatch($event, $eventName = null);
}