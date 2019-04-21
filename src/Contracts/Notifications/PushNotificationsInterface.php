<?php

namespace Canvas\Contracts;

use Namshi\Notificator\NotificationInterface;

interface PushNotificationsInterface extends NotificationInterface
{
    /**
     * Assemble Notification
     */
    public function assemble();
}
