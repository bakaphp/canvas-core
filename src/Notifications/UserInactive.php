<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Baka\Contracts\Notifications\NotificationInterface;
use Baka\Mail\Message;
use Canvas\Template;
use Phalcon\Di;
use Throwable;

class UserInactive extends Notification implements NotificationInterface
{
    protected $type = Notification::APPS;
    //protected $useQueue = true;

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
                'users-inactive',
                [
                    'entity' => $this->entity->toArray(),
                    'app' => $app,
                    'fromUser' => $this->fromUser,
                    'toUser' => $this->toUser,
                ]
            );
        } catch (Throwable $e) {
            $message = "Hi {$this->fromUser->firstname} {$this->fromUser->lastname}, you have deactivated  {$this->toUser->firstname} {$this->toUser->lastname}  in {$app->name} app. <br /><br />
            Thanks ";
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

        return $this->mail->to("rwhite@mctekk.com")
            ->subject($app->name . ' - User has been deactivated')
            ->content($this->message());
    }
}
