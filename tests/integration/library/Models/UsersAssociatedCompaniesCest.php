<?php

namespace Canvas\Tests\integration\library\Models;

use Canvas\Models\UsersAssociatedCompanies;
use IntegrationTester;

class UsersAssociatedCompaniesCest
{
    /**
     * Get the current company app.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function setConfig(IntegrationTester $I)
    {
        $userAssociateCompany = UsersAssociatedCompanies::findFirst();
        $userAssociateCompany->set(
            'test2',
            ['test' => 'test']
        );

        $I->assertEquals(
            ['test' => 'test'],
            $userAssociateCompany->get('test2')
        );
    }
}
