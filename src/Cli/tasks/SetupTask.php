<?php

namespace Canvas\Cli\Tasks;

use Phalcon\Cli\Task as PhTask;
use Canvas\Cli\Tasks\AclTask;
use Canvas\Cli\Tasks\EmailtemplatesTask;

/**
 * Class AclTask.
 *
 * @package Canvas\Cli\Tasks;
 *
 * @property \Canvas\Acl\Manager $acl
 */
class SetupTask extends PhTask
{
    /**
     * Create the default roles of the system.
     *
     * @return void
     */
    public function mainAction()
    {
        // Insert Email Templates
        $emailTemplates = new EmailtemplatesTask();
        $emailTemplates->insertUserNotificationTemplate();
    }
}
