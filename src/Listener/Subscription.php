<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Phalcon\Events\Event;

class Subscription
{
    /**
     * Event to run after starting a free trial.
     *
     * @param Event $event
     * @param [type] $subscription
     * @return void
     */
    public function afterStart(Event $event, $subscription)
    {
    }

    /**
     * Event afte the free trial ends.
     *
     * @param Event $event
     * @param [type] $subscription
     * @return void
     */
    public function afterTrialEnds(Event $event, $subscription)
    {
    }
}
