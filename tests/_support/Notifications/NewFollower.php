<?php

declare(strict_types=1);

namespace Canvas\Tests\Support\Notifications;

use Baka\Contracts\Notifications\NotificationInterface;
use Canvas\Notifications\Notification;
use Canvas\Notifications\PusherNotification;
use Phalcon\Mvc\Model;
use Canvas\Traits\Notifications\CanSendGlobalCounter;
use Phalcon\Di;

class NewFollower extends Notification implements NotificationInterface
{
    use CanSendGlobalCounter;

    /**
     * Construct.
     *
     * @param bool $groupable
     */
    public function __construct(Model $entity, ?bool $groupable = false)
    {
        parent::__construct($entity);
        
        if ($groupable) {
            $this->setGroupable(true);
            $this->setSoftCap(0);
            $this->setHardCap(10);
        }
    }

    /**
     * Notification msg.
     *
     * @return string
     */
    public function message() : string
    {
        return 'is now following you';
    }

    /**
     * Send the interaction to pusher.
     *
     * @return PusherNotification|null
     */
    public function toRealTime() : ?PusherNotification
    {
        Di::getDefault()->setShared('userData', $this->toUser);

        $payload = [
            'user' => [
                'id' => $this->entity->getId(),
                'userName' => $this->entity->displayname
            ],
            'total' => 1
        ];

        return new PusherNotification(
            'user-profile-' . $this->toUser->getId(),
            'followed',
            $payload
        );
    }
}
