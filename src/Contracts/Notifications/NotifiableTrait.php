<?php

declare(strict_types=1);

namespace Canvas\Contracts\Notifications;

use Canvas\Notifications\Notify;

trait NotifiableTrait
{
    /**
     * Notify a given User entity
     *
     * @param NotificationInterfase $notification
     * @return bool
     */
    public function notify(NotificationInterfase $notification): bool
    {
        return Notify::one($this, $notification);
    }
}