<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Contracts\Notifications\NotificationInterfase;
use Baka\Mail\Message;
use Phalcon\Di;
use Canvas\Models\Users;

class ResetPassword extends Notification implements NotificationInterfase
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

        $userExists = Users::findFirst([
            'conditions' => 'email = ?0 and is_deleted = 0',
            'bind' => [$this->entity->email]
        ]);

        if (is_object($userExists)) {
            $resetPasswordUrl = $app->url . '/user/reset/' . $userExists->user_activation_key;
        }

        return "Hi {$this->entity->email}, click the following link to reset your password: <a href='{$resetPasswordUrl}'>Reset Password</a> <br /><br />
                Thanks {$this->fromUser->firstname} {$this->fromUser->lastname} ( {$this->fromUser->currentCompany->name} ) ";
    }

    /**
     * Email body.
     *
     * @return Message|null
     */
    public function toMail(): ?Message
    {
        // return $this->mail->to($this->toUser->getEmail())
        //     ->subject('Password Updated')
        //     ->content($this->message());
        
        return $this->mail->to('rwhite@mctekk.com')
        ->subject('Password Updated')
        ->content($this->message());
    }
}
