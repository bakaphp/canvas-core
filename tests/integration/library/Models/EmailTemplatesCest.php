<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\EmailTemplates;
use IntegrationTester;

class EmailTemplatesCest
{
    /**
     * Get a filesystem entities from this system modules.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByName(IntegrationTester $I)
    {
        $emailTemplate = EmailTemplates::getByName('users-invite');
        $I->assertTrue($emailTemplate instanceof EmailTemplates);
    }
}
