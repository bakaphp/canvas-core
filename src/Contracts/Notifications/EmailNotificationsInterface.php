<?php

namespace Canvas\Contracts\Notifications;

use Namshi\Notificator\NotificationInterface;

interface EmailNotificationsInterface extends NotificationInterface
{
    /**
     * Assemble Notification
     */
    public function assemble();
}
