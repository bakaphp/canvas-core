<?php

declare(strict_types=1);

namespace Canvas;

use Phalcon\Traits\EventManagerAwareTrait;

/**
 * New event Manager to allow use to use fireToQueue.
 */
class EventsManager
{
    use EventManagerAwareTrait;

    /**
    * Checking if event manager is defined - fire event.
    *
    * @param string $event
    * @param object $source
    * @param mixed $data
    * @param boolean $cancelable
    *
    */
    public function fireToQueue($event, $source, $data = null, $cancelable = true)
    {
        if ($manager = $this->getEventsManager()) {
            /**
             * @todo add the the event manager to send to queue
             */
        }
    }
}
