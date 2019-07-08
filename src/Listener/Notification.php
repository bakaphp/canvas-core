<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Phalcon\Events\Event;
use Canvas\Contracts\Notifications\NotificationInterfase;
use Baka\Mail\Message;

class Notification
{
    /**
     * From a given mail message send it now 
     *
     * @param Event $event
     * @param Message $mail
     * @return void
     */
    public function sendMail(Event $event, Message $mail)
    {
        return $mail->sendNow();
    }

    /**
     * From a given push notification send it to the user
     *
     * @param Event $event
     * @param [type] $subscription
     * @return void
     */
    public function sendPushNotification(Event $event, NotificationInterfase $notification)
    {
    }

    /**
     * From a given notificatino send its realtime websocket
     *
     * @param Event $event
     * @param NotificationInterfase $notification
     * @return void
     */
    public function sendRealtime(Event $event, NotificationInterfase $notification)
    {
    }
}
