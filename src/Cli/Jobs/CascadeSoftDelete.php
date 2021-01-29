<?php

namespace Canvas\Cli\Jobs;

use Baka\Contracts\Queue\QueueableJobInterface;
use Baka\Jobs\Job;
use Phalcon\Di;
use Phalcon\Mvc\ModelInterface;
use Throwable;

class CascadeSoftDelete extends Job implements QueueableJobInterface
{
    protected ModelInterface $model;

    /**
     * Constructor.
     *
     * @param ModelInterface $model
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * Handle the cascade soft delete.
     *
     * @return bool
     */
    public function handle() : bool
    {
        $log = Di::getDefault()->get('log');
        try {
            $log->info('Cascade Soft Delete ' . get_class($this->model) . ' Id: ' . $this->model->getId());

            $this->model->cascadeSoftDelete();
        } catch (Throwable $e) {
            $log->error($e->getTraceAsString());
        }

        return true;
    }
}
