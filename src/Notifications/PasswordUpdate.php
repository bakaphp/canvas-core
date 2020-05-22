<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Contracts\Notifications\NotificationInterface;
use Baka\Mail\Message;
use Phalcon\Di;

class PasswordUpdate extends Notification implements NotificationInterface
{
    protected $type = Notification::APPS;
    //protected $useQueue = true;

    /**
     * Notification msg.
     *
     * @return string
     */
    public function message(): string
    {
        $app = Di::getDefault()->getApp();

        return "Hi {$this->fromUser->firstname} {$this->fromUser->lastname}, your password for {$app->name} was updated <br /><br />
                Thanks ";
    }

    /**
     * Email body.
     *
     * @return Message|null
     */
    public function toMail(): ?Message
    {
        $app = Di::getDefault()->getApp();

        return $this->mail->to($this->fromUser->getEmail())
            ->subject($app->name . ' - Password Updated')
            ->content($this->message());
    }
}
