<?php

namespace Canvas\Handlers;

use Namshi\Notificator\Notification\Handler\HandlerInterface;
use Canvas\Contracts\PushNotifications as PushNotificationsContract;
use Namshi\Notificator\NotificationInterface;
use Phalcon\Di;
use Canvas\Notifications\Mobile\Apps;
use Canvas\Notifications\Mobile\Mobile;
use Canvas\Models\Notifications;
use Canvas\Models\SystemModules;

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
