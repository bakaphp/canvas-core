<?php

declare(strict_types=1);

namespace Canvas\Traits\Notifications;

use Canvas\Models\Notifications;
use Canvas\Notifications\PusherNotification;
use Phalcon\Di;

trait CanSendGlobalCounter
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
        $totalCounter = Notifications::totalUnRead($this->toUser);
        if ($totalCounter > 0) {
            $this->fire(
                'notification:sendRealtime',
                $this->sendGlobalCounter($totalCounter)
            );
        }

        return true;
    }


    /**
     * Send the interaction to pusher.
     *
     * @return PusherNotification
     */
    public function sendGlobalCounter(int $totalCounter) : PusherNotification
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
                'total' => $totalCounter
            ]
        );
    }
}
