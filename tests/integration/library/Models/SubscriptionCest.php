<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Subscription;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;
use Phalcon\Security\Random;

class SubscriptionCest
{
    /**
     * Get the active subscription for this company app.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getActiveForThisApp(IntegrationTester $I)
    {
        $I->assertTrue(Subscription::getActiveForThisApp() instanceof Subscription);
    }

    /**
     * Get subscription by user's default company.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByDefaultCompany(IntegrationTester $I)
    {
        $I->assertTrue(Subscription::getByDefaultCompany($I->grabFromDi('userData')) instanceof Subscription);
    }

    /**
     * Search current company's app setting with key paid to verify payment status for current company.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getPaymentStatus(IntegrationTester $I)
    {
        $I->assertTrue(gettype(Subscription::getPaymentStatus($I->grabFromDi('userData'))) == 'boolean');
    }

    public function isActive(IntegrationTester $I)
    {
        $I->assertTrue(is_bool(Subscription::getActiveForThisApp()->active()));
    }

    public function paid(IntegrationTester $I)
    {
        $I->assertTrue(is_bool(Subscription::getActiveForThisApp()->paid()));
    }

    public function activate(IntegrationTester $I)
    {
        $I->assertTrue(is_bool(Subscription::getActiveForThisApp()->activate()));
    }

    public function onTrial(IntegrationTester $I)
    {
        $I->assertTrue(is_bool(Subscription::getActiveForThisApp()->onTrial()));
    }

    public function validateByGracePeriod(IntegrationTester $I)
    {
        $subscription = Subscription::getActiveForThisApp();
        $subscription->validateByGracePeriod();

        $I->assertTrue(strtotime($subscription->grace_period_ends) > 0);
    }
}
