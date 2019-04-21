<?php

namespace Canvas\Notifications\Mobile;

use Namshi\Notificator\Notification;
use Canvas\Contracts\PushNotifications as PushNotificationsContract;
use Canvas\Models\Notifications;
use Canvas\Notifications\Mobile\Mobile;
use Canvas\Traits\NotificationsTrait;

class Users extends Mobile implements PushNotificationsContract
{
    /**
     * Notifications Trait
     */
    use NotificationsTrait;

    /**
     * Assemble an Apps Push Notification
     * @todo Create specific assembler for apps push notifications
     */
    public function assemble()
    {
        /**
         * Create a new database record
         */
        self::create((array)$this->user, $this->content, Notifications::USERS, $this->systemModule);

        return $this->content . " From Users";
    }
}
