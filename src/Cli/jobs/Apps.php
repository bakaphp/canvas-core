<?php

namespace Canvas\Cli\Jobs;

use Canvas\Contracts\Queue\QueueableJobInterfase;
use Canvas\Jobs\Job;
use Phalcon\Di;
use Canvas\Models\Apps as CanvasApps;
use Phalcon\Security\Random;

class Apps extends Job implements QueueableJobInterfase
{
    /**
     * Realtime channel
     *
     * @var string
     */
    protected $appName;

    /**
     * Realtime event
     *
     * @var string
     */
    protected $appDescription;

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
    public function __construct(string $appName, string $appDescription, array $params)
    {
        $this->appName = $appName;
        $this->appDescription = $appDescription;
        $this->params = $params;
    }

    /**
     * Handle the pusher request
     * @todo New Apps can't be created, the system does not take into account the creation of other apps apart from the default. Need to change this
     * @return void
     */
    public function handle()
    {
        $random = new Random();
        $appName = $this->appName;
        $appDescription = $this->appDescription;

        $app = new CanvasApps();
        $app->name = $appName;
        $app->description = $appDescription;
        $app->key = $random->uuid();
        $app->is_public = 1;
        $app->default_apps_plan_id = 1;
        $app->created_at = date('Y-m-d H:i:s');
        $app->is_deleted = 0;
        $app->payments_active = 0;

        if (!$app->save()) {
            Di::getDefault()->getLog()->error('App could not be created');
        }

        /**
         * @todo Only works for default app
         */
        Di::getDefault()->getAcl()->setApp($app);

        Di::getDefault()->getAcl()->addRole($appName .'.Admins');
        Di::getDefault()->getAcl()->addRole($appName .'.Agents');
        Di::getDefault()->getAcl()->addRole($appName .'.Users');

        Di::getDefault()->getAcl()->addResource($appName .'.Users', ['read', 'list', 'create', 'update', 'delete']);
        Di::getDefault()->getAcl()->allow('Admins', $appName .'.Users', ['read', 'list', 'create', 'update', 'delete']);
        
        return true;
    }
}
