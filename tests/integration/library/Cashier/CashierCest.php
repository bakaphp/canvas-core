<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\Models\AppsPlans;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\Subscription;
use Carbon\Carbon;
use Exception;
use IntegrationTester;
use Stripe\StripeClient;

class CashierCest
{
    /**
     * Tests.
     */
    public function testSubscriptionsCanBeCreatedAndUpdated(IntegrationTester $I)
    {
        $defaultPlan = AppsPlans::getDefaultPlan();
        $otherPlan = AppsPlans::findFirstOrFail('id != ' . $defaultPlan->getId() . ' and apps_id = ' . $defaultPlan->apps_id);

        $companyGroup = CompaniesGroups::findFirst();

        //Create Subscription
        $companyGroup->newSubscription($defaultPlan)
        ->trialDays($defaultPlan->free_trial_dates)
        ->create();

        //$I->assertEquals(1, count($user->subscriptions));
        $I->assertNotNull($companyGroup->subscription()->stripe_id);

        $I->assertTrue($companyGroup->subscribed());
        $I->assertTrue($companyGroup->subscribedToPlan($defaultPlan));
        $I->assertFalse($companyGroup->subscribedToPlan($otherPlan));
        $I->assertTrue($companyGroup->subscription()->active());
        $I->assertFalse($companyGroup->subscription()->cancelled());
        $I->assertFalse($companyGroup->subscription()->onGracePeriod());

        //Cancel Subscription
        $subscription = $companyGroup->subscription();
        $subscription->cancel();

        $I->assertTrue($subscription->active());
        $I->assertFalse($subscription->cancelled());
        $I->assertTrue($subscription->onGracePeriod());
    }

    public function testSubscriptionSwap(IntegrationTester $I)
    {
        $defaultPlan = AppsPlans::getDefaultPlan();
        $otherPlan = AppsPlans::findFirstOrFail('id != ' . $defaultPlan->getId() . ' and apps_id = ' . $defaultPlan->apps_id);

        $companyGroup = CompaniesGroups::findFirst();

        //Create Subscription
        $companyGroup->newSubscription($defaultPlan)
         ->trialDays($defaultPlan->free_trial_dates)
         ->withMetadata(['appPlan' => $defaultPlan->getId()])
         ->create();

        $subscription = $companyGroup->subscription();

        // Update current plan
        $swap = $subscription->swap($otherPlan);

        $I->assertEquals(
            $otherPlan->stripe_plan,
            $swap->stripe_plan
        );
    }

    public function testCreatingSubscriptionWithTrial(IntegrationTester $I)
    {
        $defaultPlan = AppsPlans::getDefaultPlan();
        $otherPlan = AppsPlans::findFirstOrFail('id != ' . $defaultPlan->getId() . ' and apps_id = ' . $defaultPlan->apps_id);

        $companyGroup = CompaniesGroups::findFirst();

        //Create Subscription
        $companyGroup->newSubscription($defaultPlan)
        ->trialDays(7)
        ->create();

        $subscription = $companyGroup->subscription();

        $I->assertTrue($subscription->active());
        $I->assertTrue($subscription->onTrial());
        $dt = Carbon::parse($subscription->trial_ends_at);
        $I->assertEquals(Carbon::today()->addDays(7)->day, $dt->day);

        // Cancel Subscription
        $subscription->cancel();

        $I->assertTrue($subscription->active());
        $I->assertTrue($subscription->onGracePeriod());
    }

    public function testCancelAndActiveSub(IntegrationTester $I)
    {
        $defaultPlan = AppsPlans::getDefaultPlan();
        $otherPlan = AppsPlans::findFirstOrFail('id != ' . $defaultPlan->getId() . ' and apps_id = ' . $defaultPlan->apps_id);

        $companyGroup = CompaniesGroups::findFirst();

        //Create Subscription
        $companyGroup->newSubscription($defaultPlan)
        ->trialDays(7)
        ->create();

        $subscription = $companyGroup->subscription();

        // Cancel Subscription
        $subscription->cancel();

        //still active
        $I->assertTrue($subscription->active());
        $I->assertTrue($subscription->onGracePeriod());

        $subscription->resume();

        $I->assertTrue($subscription->active());
        $I->assertFalse($subscription->onGracePeriod());
    }

    public function testCancelNowAndActiveSub(IntegrationTester $I)
    {
        $defaultPlan = AppsPlans::getDefaultPlan();
        $otherPlan = AppsPlans::findFirstOrFail('id != ' . $defaultPlan->getId() . ' and apps_id = ' . $defaultPlan->apps_id);

        $companyGroup = CompaniesGroups::findFirst();

        //Create Subscription
        $companyGroup->newSubscription($defaultPlan)
        ->trialDays(7)
        ->create();

        $subscription = $companyGroup->subscription();

        // Cancel Subscription
        $subscription->cancelNow();

        //still active
        $I->assertFalse($subscription->active());
        $I->assertFalse($subscription->onGracePeriod());

        try {
            $subscription->resume();
        } catch (Exception $e) {
            //cant reactive a subscription if it  exceeded it grace period
            $I->assertFalse($subscription->active());
            $I->assertFalse($subscription->onGracePeriod());
        }
    }

    public function testCharge(IntegrationTester $I)
    {
        $companyGroup = CompaniesGroups::findFirst();

        $customer = $companyGroup->createOrGetStripeCustomerInfo();

        $stripe = new StripeClient(
            getenv('STRIPE_SECRET')
        );
        $payment = $stripe->paymentMethods->create([
            'type' => 'card',
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 11,
                'exp_year' => date('Y') + 1,
                'cvc' => '314',
            ],
        ]);

        $stripe->paymentMethods->attach(
            $payment->id,
            ['customer' => $customer->id]
        );

        $charge = $companyGroup->charge(1000, $payment);

        $I->assertTrue(!empty($charge->id));
        $I->assertTrue($charge->amount === 1000);
    }

    public function testRefunds(IntegrationTester $I)
    {
        $companyGroup = CompaniesGroups::findFirst();

        $customer = $companyGroup->createOrGetStripeCustomerInfo();

        $stripe = new StripeClient(
            getenv('STRIPE_SECRET')
        );
        $payment = $stripe->paymentMethods->create([
            'type' => 'card',
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 11,
                'exp_year' => date('Y') + 1,
                'cvc' => '314',
            ],
        ]);

        $stripe->paymentMethods->attach(
            $payment->id,
            ['customer' => $customer->id]
        );

        $charge = $companyGroup->charge(1000, $payment);

        // Create the refund
        $refund = $companyGroup->refund($charge->id);

        // Refund Tests
        $I->assertEquals(1000, $refund->amount);
    }

    public function testAddPlan(IntegrationTester $I)
    {
        $companyGroup = CompaniesGroups::findFirst();
        $defaultPlan = AppsPlans::getDefaultPlan();

        //Create Subscription
        $companyGroup->newSubscription($defaultPlan)
          ->trialDays($defaultPlan->free_trial_dates)
          ->create();

        $subscription = $companyGroup->subscription();

        $otherPlan = AppsPlans::findFirstOrFail('id != ' . $defaultPlan->getId() . ' and apps_id = ' . $defaultPlan->apps_id);

        $swap = $subscription->swap($defaultPlan);

        $addedPlan = $subscription->addPlan($otherPlan);

        $I->assertTrue(count($addedPlan->plans) == 2);
    }

    public function removePlan(IntegrationTester $I)
    {
        $companyGroup = CompaniesGroups::findFirst();
        $subscription = $companyGroup->subscription();

        $defaultPlan = AppsPlans::getDefaultPlan();
        $otherPlan = AppsPlans::findFirstOrFail('id != ' . $defaultPlan->getId() . ' and apps_id = ' . $defaultPlan->apps_id);

        $addedPlan = $subscription->removePlan($otherPlan);

        $I->assertTrue(count($addedPlan->plans) == 1);
    }

    /**
     * @todo
     * - missing test for items
     * - missing testing other subscription features still
     */
}
