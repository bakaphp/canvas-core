<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Contracts\Notifications\NotificationInterfase;
use Canvas\Models\Users;
use Phalcon\Di;

class Notify
{
    /**
     * Send the nofitication to all the users.
     *
     * @param array $users
     * @param NotificationInterfase $notification
     * @return void
     */
    public static function all(array $users, NotificationInterfase $notification)
    {
        foreach ($users as $user) {
            $this->one($user, $notification);
        }
    }

    /**
     * Process just one.
     *
     * @param Users $user
     * @param NotificationInterfase $notification
     * @return void
     */
    public static function one(Users $user, NotificationInterfase $notification): bool
    {
        if (Di::getDefault()->has('userData')) {
            $from = Di::getDefault()->getUserData();
        } else {
            $from = $user;
        }

        $notification->setTo($user);
        $notification->setFrom($from);

        return $notification->process();
    }
}
