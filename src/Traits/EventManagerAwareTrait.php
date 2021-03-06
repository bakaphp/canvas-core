<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Baka\Contracts\EventsManager\EventManagerAwareTrait as EventsManagerEventManagerAwareTrait;
use Baka\Queue\Queue;
use Phalcon\Di;

trait EventManagerAwareTrait
{
    use EventsManagerEventManagerAwareTrait;

    /**
     * Checking if event manager is defined - fire event to the event queue.
     *
     * @param string $event
     * @param object $source
     * @param mixed $data
     * @param bool $cancelable
     *
     */
    public function fireToQueue($event, $source, $data = null, $cancelable = true)
    {
        if ($this->getEventsManager()) {
            //specific data structure for canvas core queue
            $queueData = [
                'event' => $event,
                'source' => $source,
                'data' => $data,
            ];

            /**
             * do we know who ran this function?
             * this is important , sometimes on the event we will need the user data
             * or any company related info.
             */
            if (Di::getDefault()->has('userData')) {
                $queueData['userData'] = Di::getDefault()->get('userData');
            }

            //send to queue
            Queue::send(Queue::EVENTS, serialize($queueData));
        }
    }
}
