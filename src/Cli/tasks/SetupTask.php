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
        $acl = new AclTask();
        $emailTemplates = new EmailtemplatesTask();

        $this->acl->addRole('Default.Admins');
        $this->acl->addRole('Default.Agents');
        $this->acl->addRole('Default.Users');

        $this->acl->addResource('Default.Users', ['test-create', 'test-update', 'read', 'list', 'create', 'update', 'delete']);
        $this->acl->allow('Admins', 'Default.Users', ['read', 'list', 'create', 'update', 'delete']);

        $acl->kanvas();
        $emailTemplates->insertUserNotificationTemplate();
    }
}
