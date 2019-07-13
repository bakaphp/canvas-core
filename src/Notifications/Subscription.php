<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Contracts\Notifications\NotificationInterfase;
use Baka\Mail\Message;
use Phalcon\Di;

class Subscription extends Notification implements NotificationInterfase
{
    //protected $useQueue = true;

    /**
     * Notification msg.
     *
     * @return string
     */
    public function message(): string
    {
        $app = Di::getDefault()->getApp();

        return 'Hi ' . $this->toUser->firstname . ' your subscription to the App ' . $app->name . ' will expire at ' . $this->entity->subscription->trial_ends_at;
    }

    /**
     * Email body.
     *
     * @return Message|null
     */
    public function toMail(): ?Message
    {
        return $this->mail->to($this->toUser->getEmail())
            ->subject('Subscription Expiration Notice')
            ->content($this->message());
    }
}
