<?php

namespace Gewaer\Tests\integration\library\Models;

use Gewaer\Models\Apps;
use IntegrationTester;
use Canvas\Traits\SubscriptionPlanLimitTrait;
use Gewaer\Models\Users;
use Helper\Integration;

class SubscriptionLimitCest
{
    use SubscriptionPlanLimitTrait;

    /**
     * Confirm the default apps exist
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getModelKey(IntegrationTester $I)
    {

        $classKey = $this->getSubcriptionPlanLimitModelKey();

        $I->assertTrue($classKey == 'subscriptionlimitcest_total');
    }

    /**
     * Update activity of this model
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function updateUserActivity(IntegrationTester $I)
    {
        $I->assertTrue($this->updateAppActivityLimit());
    }
}
