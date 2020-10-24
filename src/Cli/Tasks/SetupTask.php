<?php

namespace Canvas\Cli\Tasks;

use Baka\TestCase\Phinx;
use Canvas\App\Setup;
use Canvas\Models\Apps;
use Phalcon\Cli\Task as PhTask;

class SetupTask extends PhTask
{
    /**
     * Start a fresh kanvas ecosystem.
     *
     * @return void
     */
    public function startAction()
    {
        echo 'Starting a Kanvas Ecosystem Fresh Setup' . PHP_EOL;
        echo 'Initializing migration' . PHP_EOL;
        Phinx::migrate();
        echo 'Processing Seeds' . PHP_EOL;
        Phinx::seed();

        echo 'Setting up App ACL' . PHP_EOL;
        $setup = new Setup($this->app);
        $setup->acl();

        echo 'Finish with Kanvas Setup' . PHP_EOL;
    }

    /**
     * Create new app.
     *
     * @param string $name
     *
     * @return void
     */
    public function newAppAction(string $name)
    {
        $app = new Apps();
        $app->name = $name;
        $app->description = $name;
        $app->ecosystem_auth = 1;
        $app->url = '';
        $app->default_apps_plan_id = 1;
        $app->is_actived = 1;
        $app->payments_active = 1;
        $app->is_public = 1;
        $app->saveOrFail();

        echo 'App Create ' . $app->name . PHP_EOL;
    }
}
