<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Phalcon\Events\Event;

class User
{
    /**
     * Event to run after a user signs up.
     *
     * @param Event $event
     * @param [type] $subscription
     * @return void
     */
    public function afterSignup(Event $event, $subscription)
    {
    }
}
