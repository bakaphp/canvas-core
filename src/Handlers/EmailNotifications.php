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

        /**
         * Lets log the email
         */
        Di::getDefault()->getLog()->info(json_encode($notification->assemble()));

        $content = $notification->assemble()->template;


        /**
         * Lets send the email
         */
        Di::getDefault()->getMail()
            ->to('rwhite@mctekk.com')
            ->subject('Test subject')
            ->content($content)
            ->sendNow();
    }
}
