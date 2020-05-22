<?php

namespace Canvas\Cli\Jobs;

use Canvas\Contracts\Queue\QueueableJobInterface;
use Canvas\Jobs\Job;
use Canvas\Notifications\PusherNotification;
use Phalcon\Di;

class Pusher extends Job implements QueueableJobInterface
{
    /**
     * Realtime channel.
     *
     * @var string
     */
    protected $channel;

    /**
     * Realtime event.
     *
     * @var string
     */
    protected $event;

    /**
     * Realtime params.
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor setup info for Pusher.
     *
     * @param string $channel
     * @param string $event
     * @param array $params
     */
    public function __construct(PusherNotification $pusherNotification)
    {
        $this->channel = $pusherNotification->channel;
        $this->event = $pusherNotification->event;
        $this->params = $pusherNotification->params;
    }

    /**
     * Handle the pusher request.
     *
     * @return void
     */
    public function handle()
    {
        $pusher = Di::getDefault()->getPusher();
        return $pusher->trigger($this->channel, $this->event, $this->params);
    }
}
