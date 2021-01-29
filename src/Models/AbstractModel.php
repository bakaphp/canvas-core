<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model as BakaModel;
use Canvas\Cli\Jobs\CascadeSoftDelete;

abstract class AbstractModel extends BakaModel
{
    /**
     * Define if need the key for the mode activity plan
     * this is need if we need the cleanup the name of the model activity.
     *
     * @var string
     */
    protected $subscriptionPlanLimitKey = null;
    public bool $cascadeSoftDelete = false;

    /**
     * Get the primary id of this model.
     *
     * @return int
     */
    public function getId() : int
    {
        return (int) $this->id;
    }

    /**
     * Execute an before softDelete
     *
     * @return void
     */
    public function beforeSoftDelete() : void
    {
        if($this->cascadeSoftDelete){
            CascadeSoftDelete::dispatch($this);
        }
    }
}
