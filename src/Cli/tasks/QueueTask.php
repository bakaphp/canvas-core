<?php

namespace Canvas\Cli\Tasks;

use Phalcon\Cli\Task as PhTask;
use Canvas\Models\UserLinkedSources;
use Canvas\Models\Users;
use Throwable;
use Phalcon\Di;

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
    public function mobileNotificationsAction(): void
    {
        $channel = $this->queue->channel();

        // Create the queue if it doesnt already exist.
        $channel->queue_declare(
            $queue = 'notifications',
            $passive = false,
            $durable = true,
            $exclusive = false,
            $auto_delete = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        echo ' [*] Waiting for notifications. To exit press CTRL+C', "\n";

        $callback = function ($msg) {
            $msgObject = json_decode($msg->body);

            echo ' [x] Received from system module: ',$msgObject->system_module, "\n";

            /**
             * Look for current user in database.
             */
            $currentUser = Users::findFirst($msgObject->users_id);

            /**
             * Lets determine what type of notification we are dealing with.
             */

            /**
             * Trigger Event Manager.
             */
            //Di::getDefault()->getManager()->trigger($notification);

            /**
             * Log the delivery info.
             */
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);

        $channel->basic_consume(
            $queue = 'notifications',
            $consumer_tag = '',
            $no_local = false,
            $no_ack = false,
            $exclusive = false,
            $nowait = false,
            $callback
        );

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $this->queue->close();
    }

    /**
     * Queue action for email notifications.
     * @return void
     */
    public function emailNotificationsAction(): void
    {
        $channel = $this->queue->channel();

        // Create the queue if it doesnt already exist.
        $channel->queue_declare(
            $queue = 'notifications',
            $passive = false,
            $durable = true,
            $exclusive = false,
            $auto_delete = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );

        echo ' [*] Waiting for email notifications. To exit press CTRL+C', "\n";

        $callback = function ($msg) {
            $msgObject = json_decode($msg->body);

            echo ' [x] Received from system module: ',$msgObject->system_module, "\n";

            /**
             * Look for current user in database.
             */
            $currentUser = Users::findFirst($msgObject->users_id);

            /**
             * Lets determine what type of notification we are dealing with.
             */

            /**
             * Trigger Event Manager.
             */
            //Di::getDefault()->getManager()->trigger($notification);

            /**
             * Log the delivery info.
             */
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);

        $channel->basic_consume(
            $queue = 'notifications',
            $consumer_tag = '',
            $no_local = false,
            $no_ack = false,
            $exclusive = false,
            $nowait = false,
            $callback
        );

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $this->queue->close();
    }
}
