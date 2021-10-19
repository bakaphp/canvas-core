<?php

namespace Canvas\Cli\Tasks;

use Baka\Contracts\Queue\QueueableJobInterface;
use Baka\Queue\Queue;
use Baka\Support\Str;
use Canvas\Models\Users;
use Phalcon\Cli\Task as PhTask;
use Phalcon\Mvc\Model;
use Sentry\SentrySdk;
use Throwable;

class QueueTask extends PhTask
{
    /**
     * Queue action for mobile notifications.
     *
     * @return void
     */
    public function mainAction() : void
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
        $callback = function ($msg) : void {
            //check the db before running anything
            $this->reconnectDb();

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
        $callback = function (object $msg) : void {
            //check the db before running anything
            $this->reconnectDb();

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
                echo 'Attribute notification has to be a Notification' . PHP_EOL;
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

            //run notify for the specify user
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
    public function jobsAction(?string $queueName = null)
    {
        $queue = is_null($queueName) ? QUEUE::JOBS : $queueName;

        $callback = function (object $msg) : void {
            $sentryClient = SentrySdk::getCurrentHub()->getClient();

            try {
                //check the db before running anything
                $this->reconnectDb();

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
                    echo 'This Job is not queueable ' . $msg->delivery_info['consumer_tag'];
                    $this->log->error('This Job is not queueable ' . $msg->delivery_info['consumer_tag']);
                    return;
                }

                go(function () use ($job, $msg, $sentryClient) {
                    //instance notification and pass the entity
                    try {
                        $this->reconnectDb();

                        $result = $job['job']->handle();

                        $this->log->info(
                            "Job ({$job['class']}) ran for {$job['userData']->getEmail()} - Process ID " . $msg->delivery_info['consumer_tag'],
                            [$result]
                        );
                    } catch (Throwable $e) {
                        $this->log->error(
                            $e->getMessage(),
                            [
                                $e->getTraceAsString(),
                            ]
                        );

                        $sentryClient->flush();
                    }
                });
            } catch (Throwable $e) {
                $this->log->error(
                    $e->getMessage(),
                    [
                        $e->getTraceAsString(),
                    ]
                );

                $sentryClient->flush();
            }
        };

        Queue::process($queue, $callback);
    }

    /**
     * Reconnect to our db providers.
     *
     * @return void
     */
    protected function reconnectDb() : void
    {
        //list all of our di
        $listOfServices = array_keys($this->di->getServices());

        foreach ($listOfServices as $service) {
            //find all db providers
            if (Str::contains(strtolower($service), 'db')) {
                $this->isDbConnected($service);
            }
        }

        return;
    }

    /**
     * Confirm if the db is connected.
     *
     * @return bool
     */
    protected function isDbConnected(string $dbProvider) : bool
    {
        try {
            $this->di->get($dbProvider)->fetchAll('SELECT 1');
        } catch (Throwable $e) {
            if (Str::contains($e->getMessage(), 'MySQL server has gone away') ||
                Str::contains($e->getMessage(), 'Connection timed out')) {
                $this->di->get($dbProvider)->connect();
                return true;
            }
            return false;
        }
        return true;
    }
}
