<?php
declare(strict_types=1);

namespace Canvas\Models;

abstract class AbstractModel extends \Baka\Database\Model
{
    /**
     * Define if need the key for the mode activity plan
     * this is need if we need the cleanup the name of the model activity
     *
     * @var string
     */
    protected $subscriptionPlanLimitKey = null;
}
