<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Baka\Contracts\Notifications\NotificationInterface;
use Baka\Mail\Message;
use Canvas\Template;
use Phalcon\Di;

class Signup extends Notification implements NotificationInterface
{
    //protected $useQueue = true;

    /**
     * Notification msg.
     *
     * @return string
     */
    public function message() : string
    {
        $app = Di::getDefault()->get('app');

        return Template::generate(
            'users-registration',
            [
                'entity' => $this->entity->toArray(),
                'app' => $app
            ]
        );
    }

    /**
     * Email body.
     *
     * @return Message|null
     */
    public function toMail() : ?Message
    {
        $app = Di::getDefault()->getApp();

        return $this->mail->to($this->entity->getEmail())
            ->subject('Welcome to ' . $app->name)
            ->content($this->message());
    }
}
