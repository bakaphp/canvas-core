<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Baka\Contracts\Notifications\NotificationInterface;
use Baka\Mail\Message;
use Phalcon\Di;

class ResetPassword extends Notification implements NotificationInterface
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

        $resetPasswordUrl = $app->url . '/users/reset-password/' . $this->fromUser->user_activation_forgot;

        return "Hi {$this->fromUser->firstname} {$this->fromUser->lastname}, click the following link to reset your password: <a href='{$resetPasswordUrl}'>Reset Password</a> <br /><br />
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
            ->subject($app->name .' - Password Reset')
            ->content($this->message());
    }
}
