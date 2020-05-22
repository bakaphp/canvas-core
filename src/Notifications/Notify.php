<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Contracts\Auth\UserInterface;
use Canvas\Contracts\Notifications\NotificationInterface;
use Canvas\Models\Users;
use Phalcon\Di;

class Notify
{
    /**
     * Send the nofitication to all the users.
     *
     * @param array | ResultsetInterface $users
     * @param NotificationInterface $notification
     *
     * @return void
     */
    public static function all($users, NotificationInterface $notification)
    {
        foreach ($users as $user) {
            self::one($user, $notification);
        }
    }

    /**
     * Process just one.
     *
     * @param Users $user
     * @param NotificationInterface $notification
     *
     * @return void
     */
    public static function one(UserInterface $user, NotificationInterface $notification) : bool
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
