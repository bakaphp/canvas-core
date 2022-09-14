<?php

namespace Canvas\Tests\integration\library\Webhooks;

use Canvas\Models\Companies;
use IntegrationTester;

class CompanyCest
{
    public function delete(IntegrationTester $I) : void
    {
        $company = Companies::find(['order' => 'id DESC', 'limit' => 1]);

        $I->assertTrue($company->getFirst()->delete());
    }
}
