<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\UserLinkedSources;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;
use Phalcon\Security\Random;

class UserLinkedSourcesCest
{
    /**
     * Create a new User Linked Sources record
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function createUserLinkedSources(IntegrationTester $I)
    {
        $random = new Random();

        $userLinkedSource = new UserLinkedSources();
        $userLinkedSource->users_id = $I->grabFromDi('userData')->getId();
        $userLinkedSource->source_id = 2;
        $userLinkedSource->source_users_id = $random->base58();
        $userLinkedSource->source_users_id_text = $userLinkedSource->source_users_id;
        $userLinkedSource->save();
        $I->assertTrue($userLinkedSource instanceof UserLinkedSources);
    }

    /**
     * Get mobile User Linked Sources
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getMobileUserLinkedSources(IntegrationTester $I)
    {   $userLinkedSource = UserLinkedSources::getMobileUserLinkedSources($I->grabFromDi('userData')->getId());
        $I->assertTrue(gettype($userLinkedSource) == 'array');
    }
}
