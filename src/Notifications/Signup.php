<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Baka\Contracts\Notifications\NotificationInterface;
use Baka\Mail\Message;
use Canvas\Models\EmailTemplates;
use Phalcon\Di;
use Canvas\Models\Users;
use Canvas\Template;

class Signup extends Notification implements NotificationInterface
{
    //protected $useQueue = true;

    /**
     * Notification msg.
     *
     * @return string
     */
    public function message(): string
    {
        return Template::generate('users-registration', $this->entity->toArray());
    }

    /**
     * Email body.
     *
     * @return Message|null
     */
    public function toMail(): ?Message
    {
        $app = Di::getDefault()->getApp();

        return $this->mail->to($this->entity->getEmail())
            ->subject('Welcome to ' . $app->name)
            ->content($this->message());
    }
}
