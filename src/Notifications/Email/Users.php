<?php

namespace Canvas\Notifications\Email;

use Namshi\Notificator\Notification;
use Canvas\Contracts\Notifications\EmailNotificationsInterface as EmailNotificationsContract;
use Canvas\Models\Notifications;
use Canvas\Notifications\Mobile\Mobile;
use Canvas\Traits\NotificationsTrait;

class Users extends Email implements EmailNotificationsContract
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

        //Fetch and return specific template for Apps Email Notifications
        return $this->content . " From Users";
    }
}
