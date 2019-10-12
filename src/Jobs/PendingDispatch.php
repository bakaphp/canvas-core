<?php

declare(strict_types=1);

namespace Canvas\Jobs;

use Canvas\Contracts\Queue\QueueableJobInterfase;
use Canvas\Queue\Queue;
use Phalcon\Di;

class PendingDispatch
{
    /**
     * The job.
     *
     * @var mixed
     */
    protected $job;

    /**
     * Create a new pending job dispatch.
     *
     * @param  QueueableJobInterfase  $job
     * @return void
     */
    public function __construct(QueueableJobInterfase $job)
    {
        $this->job = $job;
    }

    /**
     * Set the desired queue for the job.
     *
     * @param  string  $queue
     * @return $this
     */
    public function onQueue(string $queue)
    {
        $this->job->onQueue($queue);
        return $this;
    }

    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        $jobData = [
            'userData' => Di::getDefault()->getUserData(),
            'class' => get_class($this->job),
            'job' => $this->job
        ];

        return Queue::send($this->job->queue, serialize($jobData));
    }
}
