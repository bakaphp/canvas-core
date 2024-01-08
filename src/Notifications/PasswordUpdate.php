<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Baka\Contracts\Notifications\NotificationInterface;
use Baka\Mail\Message;
use Canvas\Template;
use Phalcon\Di;
use Throwable;

class PasswordUpdate extends Notification implements NotificationInterface
{
    protected $type = Notification::APPS;

    /**
     * Notification msg.
     *
     * @return string
     */
    public function message() : string
    {
        $app = Di::getDefault()->get('app');

        try {
            $message = Template::generate(
                'users-password-update',
                [
                    'entity' => $this->entity->toArray(),
                    'app' => $app,
                    'fromUser' => $this->fromUser,
                    'toUser' => $this->toUser,
                ]
            );
        } catch (Throwable $e) {
            $message = "Hi {$this->fromUser->firstname} {$this->fromUser->lastname}, your password for {$app->name} was updated. Thanks ";
        }

        return $message;
    }

    /**
     * Email body.
     *
     * @return Message|null
     */
    public function toMail() : ?Message
    {
        $app = Di::getDefault()->get('app');

        return $this->mail->to($this->fromUser->getEmail())
            ->subject($app->name . ' - Password Updated')
            ->content($this->message());
    }
}
