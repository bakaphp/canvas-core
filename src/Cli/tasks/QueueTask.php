<?php

namespace Canvas\Cli\Tasks;

use Canvas\Contracts\Queue\QueueableJobInterface;
use Phalcon\Cli\Task as PhTask;
use Canvas\Models\Users;
use Canvas\Queue\Queue;
use Phalcon\Mvc\Model;
use Throwable;

/**
 * CLI To send push notification and pusher msg.
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
        echo 'Canvas Ecosystem Queue Jobs: events | notifications | jobs' . PHP_EOL;
    }

    /**
     * Queue to process internal Canvas Events.
     *
     * @return void
     */
    public function eventsAction()
    {
        $callback = function ($msg) {
            //check the db before running anything
            if (!$this->isDbConnected('db')) {
                return ;
            }

            if ($this->di->has('dblocal')) {
                if (!$this->isDbConnected('dblocal')) {
                    return ;
                }
            }

            //we get the data from our event trigger and unserialize
            $event = unserialize($msg->body);

            //overwrite the user who is running this process
            if (isset($event['userData']) && $event['userData'] instanceof Users) {
                $this->di->setShared('userData', $event['userData']);
            }

            //lets fire the event
            $this->events->fire($event['event'], $event['source'], $event['data']);

            $this->log->info(
                "Notification ({$event['event']}) - Process ID " . $msg->delivery_info['consumer_tag']
            );
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
            //check the db before running anything
            if (!$this->isDbConnected('db')) {
                return ;
            }

            if ($this->di->has('dblocal')) {
                if (!$this->isDbConnected('dblocal')) {
                    return ;
                }
            }

            //we get the data from our event trigger and unserialize
            $notification = unserialize($msg->body);

            //overwrite the user who is running this process
            if ($notification['from'] instanceof Users) {
                $this->di->setShared('userData', $notification['from']);
            }

            if (!$notification['to'] instanceof Users) {
                echo 'Attribute TO has to be a User' . PHP_EOL;
                return;
            }

            if (!class_exists($notification['notification'])) {
                echo 'Attribute notification has to be a Notificatoin' . PHP_EOL;
                return;
            }
            $notificationClass = $notification['notification'];

            if (!$notification['entity'] instanceof Model) {
                echo 'Attribute entity has to be a Model' . PHP_EOL;
                return;
            }

            $user = $notification['to'];

            //instance notification and pass the entity
            $notification = new $notification['notification']($notification['entity']);
            //disable the queue so we process it now
            $notification->disableQueue();

            //run notify for the specifiy user
            $user->notify($notification);

            $this->log->info(
                "Notification ({$notificationClass}) sent to {$user->email} - Process ID " . $msg->delivery_info['consumer_tag']
            );
        };

        Queue::process(QUEUE::NOTIFICATIONS, $callback);
    }

    /**
     * Queue to process Canvas Jobs.
     *
     * @return void
     */
    public function jobsAction(array $params)
    {
        $queue = !isset($params[0]) ? QUEUE::JOBS : $params[0];

        $callback = function ($msg) {
            //check the db before running anything
            if (!$this->isDbConnected('db')) {
                return ;
            }

            if ($this->di->has('dblocal')) {
                if (!$this->isDbConnected('dblocal')) {
                    return ;
                }
            }

            //we get the data from our event trigger and unserialize
            $job = unserialize($msg->body);

            //overwrite the user who is running this process
            if ($job['userData'] instanceof Users) {
                $this->di->setShared('userData', $job['userData']);
            }

            if (!class_exists($job['class'])) {
                echo 'No Job class found' . PHP_EOL;
                $this->log->error('No Job class found ' . $job['class']);
                return;
            }

            if (!$job['job'] instanceof QueueableJobInterface) {
                echo 'This Job is not queable ' . $msg->delivery_info['consumer_tag'] ;
                $this->log->error('This Job is not queable ' . $msg->delivery_info['consumer_tag']);
                return;
            }

            /**
             * swoole coroutine.
             */
            go(function () use ($job, $msg) {
                //instance notification and pass the entity
                $result = $job['job']->handle();

                $this->log->info(
                    "Job ({$job['class']}) ran for {$job['userData']->getEmail()} - Process ID " . $msg->delivery_info['consumer_tag'],
                    [$result]
                );
            });
        };

        Queue::process($queue, $callback);
    }

    /**
     * Confirm if the db is connected.
     *
     * @return boolean
     */
    protected function isDbConnected(string $dbProvider): bool
    {
        try {
            $this->di->get($dbProvider)->fetchAll('SELECT 1');
        } catch (Throwable $e) {
            if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                $this->di->get($dbProvider)->connect();
                return true;
            }
            return false;
        }
        return true;
    }
}
