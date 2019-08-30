<?php

namespace Canvas\Cli\Jobs;

use Canvas\Contracts\Queue\QueueableJobInterfase;
use Canvas\Jobs\Job;
use Phalcon\Di;

class Pusher extends Job implements QueueableJobInterfase
{
    /**
     * Realtime channel
     *
     * @var string
     */
    protected $channel;

    /**
     * Realtime event
     *
     * @var string
     */
    protected $event;

    /**
     * Realtime params
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor setup info for Pusher
     *
     * @param string $channel
     * @param string $event
     * @param array $params
     */
    public function __construct(string $channel, string $event, array $params)
    {
        $this->channel = $channel;
        $this->event = $event;
        $this->params = $params;
    }

    /**
     * Handle the pusher request
     *
     * @return void
     */
    public function handle()
    {
        $pusher = Di::getDefault()->getPusher();
        return $pusher->trigger($this->channel, $this->event, $this->params);
    }
}
