<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Models\Users;

class PusherNotification
{
    /**
     * @var string
     */
    public $channel;

    /**
     * @var string
     */
    public $event;

    /**
     * Additional params if needed.
     *
     * @var array
     */
    public $params;

    /**
     * Init a push notification object.
     *
     * @param string $channel
     * @param string $event
     * @param array $params
     *
     * @return void
     */
    public function __construct(string $channel, string $event, ?array $params = null)
    {
        $this->channel = $channel;
        $this->event = $event;
        $this->params = $params;
    }
}
