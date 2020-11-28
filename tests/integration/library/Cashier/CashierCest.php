<?php

namespace Gewaer\Tests\integration\library\Jobs;

use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\Companies;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\Subscription;
use Canvas\Models\Users;
use Carbon\Carbon;
use IntegrationTester;
use Stripe\Token;

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

        $I->assertFalse($subscription->active());
        $I->assertTrue($subscription->cancelled());
        $I->assertFalse($subscription->onGracePeriod());
    }

    public function testSubscriptionSwap(IntegrationTester $I)
    {
        $defaultPlan = AppsPlans::getDefaultPlan();
        $otherPlan = AppsPlans::findFirstOrFail('id != ' . $defaultPlan->getId() . ' and apps_id = ' . $defaultPlan->apps_id);

        $companyGroup = CompaniesGroups::findFirst();

        //Create Subscription
        $companyGroup->newSubscription($defaultPlan)
         ->trialDays($defaultPlan->free_trial_dates)
         ->create();

        $subscription = $companyGroup->subscription();

        // Update current plan
        $subscription->swap($otherPlan);

        $I->assertEquals('monthly-10-2', $subscription->stripe_plan);
    }

    public function testCreatingSubscriptionWithTrial(IntegrationTester $I)
    {
        $user = Users::findFirstOrFail([
            'conditions' => 'stripe_id is null',
            'order' => 'RAND()'
        ]);

        $company = Companies::findFirstOrFail([
            'order' => 'RAND()',
        ]);
        $apps = Apps::findFirstOrFail(1);

        // Create Subscription
        $user->newSubscription('main', 'monthly-10-1', $company, $apps)
            ->trialDays(7)->create($this->getTestToken());

        $subscription = $user->subscription('main');

        $I->assertTrue($subscription->active());
        $I->assertTrue($subscription->onTrial());
        $dt = Carbon::parse($subscription->trial_ends_at);
        $I->assertEquals(Carbon::today()->addDays(7)->day, $dt->day);

        // Cancel Subscription
        $subscription->cancel();

        $I->assertFalse($subscription->active());
        $I->assertFalse($subscription->onGracePeriod());
    }

    public function testCreatingOneOffInvoices(IntegrationTester $I)
    {
        $user = Users::findFirstOrFail([
            'conditions' => 'stripe_id is not null',
            'order' => 'RAND()'
        ]);

        // Create Invoice
        $user->createAsStripeCustomer($this->getTestToken());
        $user->invoiceFor('Phalcon PHP Cashier', 1000);

        // Invoice Tests
        $invoice = $user->invoices()[0];
        $I->assertEquals('$10.00', $invoice->total());
        $I->assertEquals('Phalcon PHP Cashier', $invoice->invoiceItems()[0]->asStripeInvoiceItem()->description);
    }

    public function testRefunds(IntegrationTester $I)
    {
        $user = Users::findFirstOrFail([
            'conditions' => 'stripe_id is not null',
            'order' => 'RAND()'
        ]);
        // Create Invoice
        $user->createAsStripeCustomer($this->getTestToken());
        $invoice = $user->invoiceFor('Phalcon PHP Cashier', 1000);

        // Create the refund
        $refund = $user->refund($invoice->charge);

        // Refund Tests
        $I->assertEquals(1000, $refund->amount);
    }

    protected function getTestToken(IntegrationTester $I)
    {
        return Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => date('m', strtotime('+1 month')),
                'exp_year' => date('Y', strtotime('+1 year')),
                'cvc' => '123',
            ],
        ], ['api_key' => getenv('STRIPE_SECRET')])->id;
    }
}
