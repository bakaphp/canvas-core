<?php

namespace Canvas\Cli\Jobs;

use Baka\Contracts\Queue\QueueableJobInterface;
use Baka\Jobs\Job;
use Canvas\App\Setup;
use Canvas\Models\Apps as CanvasApps;

class Apps extends Job implements QueueableJobInterface
{
    protected CanvasApps $app;
    protected Setup $setup;

    /**
     * Constructor setup info for Pusher.
     *
     * @param string $channel
     * @param string $event
     * @param array $params
     */
    public function __construct(CanvasApps $app)
    {
        $this->app = $app;
        $this->setup = new Setup($this->app);
    }

    /**
     * Handle the pusher request.
     *
     * @todo New Apps can't be created, the system does not take into account the creation of other apps apart from the default. Need to change this
     *
     * @return void
     */
    public function handle()
    {
        /**
         * - apps plans
         * - settings
         * - roles
         * - email template
         * - menus
         * - system modules
         * -.
         */

        $this->setup
            ->settings()
            ->plans()
            ->acl()
            ->systemModules();

        return true;
    }
}
