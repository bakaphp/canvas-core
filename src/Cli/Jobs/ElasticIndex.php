<?php

namespace Canvas\Cli\Jobs;

use Baka\Contracts\Queue\QueueableJobInterface;
use Baka\Elasticsearch\IndexBuilder;
use Baka\Jobs\Job;
use Phalcon\Di;
use Phalcon\Mvc\ModelInterface;
use Throwable;

class ElasticIndex extends Job implements QueueableJobInterface
{
    protected ModelInterface $model;
    protected int $maxDepth = 1;

    /**
     * Constructor.
     *
     * @param ModelInterface $model
     */
    public function __construct(ModelInterface $model, int $maxDepth = 1)
    {
        $this->model = $model;
        $this->maxDepth = $maxDepth;
    }

    /**
     * Handle the pusher request.
     *
     * @return void
     */
    public function handle()
    {
        $log = Di::getDefault()->get('log');
        try {
            $this->log->info('Index to Elastic ' . get_class($this->model) . ' Id: ' . $model->getId());

            // Get elasticsearch class handler instance
            $elasticsearch = new IndexBuilder();

            //insert into elastic
            $elasticsearch->indexDocument($this->model, $this->maxDepth);
        } catch (Throwable $e) {
            $log->error($e->getTraceAsString());
        }

        return true;
    }
}
