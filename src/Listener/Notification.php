<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Phalcon\Events\Event;
use Canvas\Contracts\Notifications\NotificationInterfase;
use Baka\Mail\Message;
use Canvas\Cli\Jobs\PushNotifications;
use Canvas\Models\Users;
use Canvas\Notifications\PushNotification;

class Notification
{
    /**
     * From a given mail message send it now.
     *
     * @param Event $event
     * @param Message $mail
     * @return mixed
     */
    public function sendMail(Event $event, Message $mail)
    {
        return $mail->sendNow();
    }

    /**
     * From a given push notification send it to the user.
     *
     * @param Event $event
     * @param Users $user
     * @param string $message
     * @param array $params
     * @return mixed
     */
    public function sendPushNotification(Event $event, PushNotification $pushNotification)
    {
        return PushNotifications::dispatch($pushNotification);
    }

    /**
     * From a given notificatino send its realtime websocket.
     *
     * @param Event $event
     * @param NotificationInterfase $notification
     * @return void
     */
    public function sendRealtime(Event $event, NotificationInterfase $notification)
    {
    }
}
