<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Models\Users;

class PushNotification
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $icon;

    /**
     * User Object
     * 
     * @var Users
     */
    public $to;

    /**
     * Additional params if needed
     *
     * @var array
     */
    public $params;

    /**
     * Init a push notification object
     *
     * @param Users $user
     * @param string $title
     * @param string $message
     * @param array $params
     * 
     * @return void
     */
    public function __construct(Users $user, string $title, string $message, ?array $params = null)
    {
        $this->to = $user;
        $this->title = $title;
        $this->message = $message;
        $this->params = $params;
    }
}
