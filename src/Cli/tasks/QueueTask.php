<?php

namespace Canvas\Cli\Tasks;

use Phalcon\Cli\Task as PhTask;
use Canvas\Models\Users;
use Canvas\Queue\Queue;

/**
 * CLI To send push ontification and pusher msg.
 *
 * @package Canvas\Cli\Tasks
 *
 * @property Config $config
 * @property \Pusher\Pusher $pusher
 * @property \Monolog\Logger $log
 * @property Channel $channel
 * @property Queue $queue
 *
 */
class QueueTask extends PhTask
{

    /**
     * Queue action for mobile notifications.
     * @return void
     */
    public function mainAction(array $params): void
    {
        if (empty($queueName = $params[0])) {
            echo 'Need a queue name on your first params';
            return;
        }

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };

        //process the queue
        Queue::process($queueName, $callback);
    }

    /**
     * Queue to process internal Canvas Events.
     *
     * @return void
     */
    public function eventsAction()
    {
        $callback = function ($msg) {


            //we get the data from our event trigger and unserialize 
            $event = unserialize($msg->body);

            //overwrite the user who is running this process
            if (isset($event['userData']) && $event['userData'] instanceof Users) {
                $this->di->setShared('userData', $event['userData']);
            }

            //lets fire the event
            $this->events->fire($event['event'], $event['source'], $event['data']);
           // $events->fire('user:test', Users::findFirst(), ['d']);

        };

        Queue::process(QUEUE::EVENTS, $callback);
    }

    /**
     * Queue to process internal Canvas Events.
     *
     * @return void
     */
    public function notificationsAction()
    {
        $callback = function ($msg) {


            //we get the data from our event trigger and unserialize 
            $notification = unserialize($msg->body);

            //overwrite the user who is running this process
            if (isset($notification['from']) && $notification['from'] instanceof Users) {
                $this->di->setShared('userData', $notification['from']);
            }

            //lets fire the event
            $notification->notification->process();

        };

        Queue::process(QUEUE::EVENTS, $callback);
    }
}
