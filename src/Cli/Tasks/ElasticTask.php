<?php

namespace Canvas\Cli\Tasks;

use Baka\Contracts\Elasticsearch\IndexBuilderTaskTrait;
use Phalcon\Cli\Task as PhTask;

class ElasticTask extends PhTask
{
    use IndexBuilderTaskTrait;
}
