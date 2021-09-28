<?php

declare(strict_types=1);

namespace Canvas\Traits\Notifications;

use Canvas\Models\Notifications;
use Canvas\Notifications\PusherNotification;
use Phalcon\Di;

trait SendGlobalCounter
{
    /**
     * Send the notification to the places the user defined.
     *
     * @return bool
     */
    public function trigger() : bool
    {
        parent::trigger();

        //send to pusher
        $this->fire(
            'notification:sendRealtime',
            $this->sendGlobalCounter()
        );

        return true;
    }


    /**
     * Send the interaction to pusher.
     *
     * @return PusherNotification
     */
    public function sendGlobalCounter() : PusherNotification
    {
        Di::getDefault()->setShared('userData', $this->toUser);

        return new PusherNotification(
            'user-profile-' . $this->toUser->getId(),
            'notificationsCounter',
            [
                'user' => [
                    'id' => $this->toUser->getId(),
                    'userName' => $this->toUser->displayname
                ],
                'total' => Notifications::totalUnRead($this->toUser)
            ]
        );
    }
}
