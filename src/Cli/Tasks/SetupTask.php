<?php

namespace Canvas\Cli\Tasks;

use function Baka\appPath;
use Canvas\App\Setup;
use Canvas\Models\Apps;
use Canvas\Models\SystemModules;
use Phalcon\Cli\Task as PhTask;
use Phalcon\Utils\Slug;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;

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

        $phinxApp = new PhinxApplication();
        $phinxTextWrapper = new TextWrapper($phinxApp);
        $configFile = 'phinx-kanvas.php';
        $parser = 'php';

        $phinxTextWrapper->setOption('configuration', appPath($configFile));
        $phinxTextWrapper->setOption('parser', $parser);

        $phinxTextWrapper->getMigrate();
        echo 'Processing Seeds' . PHP_EOL;
        $phinxTextWrapper->getSeed();

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

        $setup = new Setup($app);
        $setup->settings()
            ->plans()
            ->acl()
            ->systemModules()
            ->emailTemplates()
            ->defaultMenus();

        echo 'App Create ' . $app->name . PHP_EOL;
    }

    /**
     * Create a new system module from name a namespace.
     *
     * @param string $name
     * @param string $model
     *
     * @return void
     */
    public function createSystemModuleAction(string $name, string $model) : void
    {
        if (!class_exists($model)) {
            echo 'We expecting the complete class name with namespace ' . PHP_EOL;
        }

        $newSystemModule = new SystemModules();
        $newSystemModule->name = $name;
        $newSystemModule->slug = Slug::generate($name);
        $newSystemModule->model_name = $model;
        $newSystemModule->apps_id = $this->app->getId();
        $newSystemModule->name = $name;
        $newSystemModule->saveOrFail();

        echo $name . 'was saved on system module as' . $model . PHP_EOL;
    }
}
