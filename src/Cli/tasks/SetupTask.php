<?php

namespace Canvas\Cli\Tasks;

use Canvas\Models\Apps;
use Phalcon\Cli\Task as PhTask;

class SetupTask extends PhTask
{
    /**
     * Create the default roles of the system.
     *
     * @return void
     */
    public function mainAction()
    {
        $acl = new AclTask();
        $emailTemplates = new EmailtemplatesTask();

        $acl->setupDefaultRoles();
        $acl->kanvas();
        $emailTemplates->insertUserNotificationTemplate();
    }

    /**
     * Create new app.
     *
     * @param string $name
     *
     * @return void
     */
    public function appAction(string $name)
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
