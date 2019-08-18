<?php

declare(strict_types=1);

namespace Canvas\Jobs;

use Canvas\Contracts\Queue\QueueableJobInterfase;
use Canvas\Contracts\Queue\QueueableTrait;

abstract class Job implements QueueableJobInterfase
{
    use QueueableTrait;

    /**
     * Execute de Jobs.
     *
     * @return void
     */
    abstract public function handle();

    /**
     * Dispatch the job with the given arguments.
     *
     * @param mixed $mixed
     * @return void
     */
    public static function dispatch(): PendingDispatch
    {
        return new PendingDispatch(new static(...func_get_args()));
    }
}
