<?php

namespace Canvas\Handlers;

use Namshi\Notificator\Notification\Handler\HandlerInterface;
use Canvas\Contracts\Notifications\EmailNotificationsInterface as EmailNotificationsContract;
use Namshi\Notificator\NotificationInterface;
use Phalcon\Di;
use Canvas\Models\Notifications;
use Canvas\Models\SystemModules;

class EmailNotifications implements HandlerInterface
{
    /**
     * Stablishes type of handler
     */
    public function shouldHandle(NotificationInterface $notification)
    {
        return $notification instanceof EmailNotificationsContract;
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
