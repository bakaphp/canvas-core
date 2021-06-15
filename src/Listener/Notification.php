<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Baka\Mail\Message;
use Canvas\Cli\Jobs\Pusher;
use Canvas\Cli\Jobs\PushNotifications;
use Canvas\Notifications\PusherNotification;
use Canvas\Notifications\PushNotification;
use Phalcon\Events\Event;

class Notification
{
    /**
     * From a given mail message send it now.
     *
     * @param Event $event
     * @param Message $mail
     *
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
     * @param PusherNotification $pusherNotification
     *
     * @return void
     */
    public function sendPushNotification(Event $event, PushNotification $pushNotification)
    {
        return PushNotifications::dispatch($pushNotification);
    }

    /**
     * From a given notificatino send its realtime websocket.
     *
     * @param Event $event
     * @param PusherNotification $pusherNotification
     *
     * @return void
     */
    public function sendRealtime(Event $event, PusherNotification $pusherNotification)
    {
        return Pusher::dispatch($pusherNotification);
    }
}
