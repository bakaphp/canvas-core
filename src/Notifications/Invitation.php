<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Contracts\Notifications\NotificationInterface;
use Baka\Mail\Message;
use Phalcon\Di;
use Canvas\Models\Users;

class Invitation extends Notification implements NotificationInterface
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

        $invitationUrl = $app->url . '/users/invites/' . $this->entity->invite_hash;

        if (is_object($userExists)) {
            $invitationUrl = $app->url . '/users/link/' . $this->entity->invite_hash;
        }

        return "Hi {$this->entity->email} you have been invite to the app {$app->name} to create you account please <a href='{$invitationUrl}'>click here</a> <br /><br />
                Thanks {$this->fromUser->firstname} {$this->fromUser->lastname} ( {$this->fromUser->getDefaultCompany()->name} ) ";
    }

    /**
     * Email body.
     *
     * @return Message|null
     */
    public function toMail(): ?Message
    {
        return $this->mail->to($this->toUser->getEmail())
            ->subject('You have been invited!')
            ->content($this->message());
    }
}
