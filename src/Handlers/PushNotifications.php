<?php

namespace Canvas\Handlers;

use Namshi\Notificator\Notification\Handler\HandlerInterface;
use Canvas\Contracts\Notifications\PushNotificationsInterface as PushNotificationsContract;
use Namshi\Notificator\NotificationInterface;
use Phalcon\Di;

class PushNotifications implements HandlerInterface
{
    /**
     * Stablishes type of handler
     */
    public function shouldHandle(NotificationInterface $notification)
    {
        return $notification instanceof PushNotificationsContract;
    }
    
    /**
     * Handles actions to take depending of notifications
     * @param NotificationInterface $notification
     */
    public function handle(NotificationInterface $notification)
    {

        //Push the notification.In this case we are just logging the info
        Di::getDefault()->getLog()->info($notification->assemble());
    }
}
