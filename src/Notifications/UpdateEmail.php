<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Contracts\Notifications\NotificationInterfase;
use Baka\Mail\Message;
use Phalcon\Di;
use Canvas\Template;

class UpdateEmail extends Notification implements NotificationInterfase
{
    //protected $useQueue = true;

    /**
     * Notification msg.
     *
     * @return string
     */
    public function message(): string
    {
        //$emailChangeUrl = $this->config->app->frontEndUrl . '/user/' . $user->user_activation_email . '/email';
        //$subject = _('Email Change Request');
        //$body = sprintf(_('Click %shere%s to set a new email for your account.'), '<a href="' . $emailChangeUrl . '">', '</a>');
        //break;

        return Template::generate('update-email', $this->entity->toArray());
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
            ->subject('Confirm email update in ' . $app->name)
            ->content($this->message());
    }
}
