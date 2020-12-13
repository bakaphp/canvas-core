<?php

use Baka\Http\Exception\UnauthorizedException;
use Canvas\Models\Subscription;
use Canvas\Models\Users;
use Canvas\Tests\api\PaymentsCest;

class AppsPlanCest
{
    /**
     * Create subscription.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function upgrade(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $this->undeleteSubscriptions();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPut('/v1/apps-plans/monthly-10-2');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data['id']));
    }

    /**
     * Create subscription.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function downgrade(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $this->undeleteSubscriptions();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPut('/v1/apps-plans/monthly-10-1');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data['id']));
    }

    /**
     * Create subscription.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function cancelSubscription(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendDelete('/v1/apps-plans/monthly-10-1');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        //we need to update all subscriptions for other test
        $this->undeleteSubscriptions();

        $I->assertTrue(isset($data['id']));
    }

    /**
     * We need to make sure we dont have the current subscription delete by other test.
     *
     * @return void
     */
    public function undeleteSubscriptions()
    {
        //we need to update all subscriptions for other test
        $subscriptions = Subscription::find();
        foreach ($subscriptions as $subscription) {
            $subscription->is_deleted = 0;
            $subscription->update();
        }
    }

    /**
     * Free Trial Ending Test.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function freeTrialEndingSubscription(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->haveHttpHeader('Stripe-Signature', PaymentsCest::getStripeSignature());
        $I->sendPost('/v1/' . 'webhook/payments', [
            'type' => 'customer.subscription.trial_will_end',
            'data' => [
                'object' => [
                    'customer' => $userData->stripe_id,
                    'trial_end' => 1549737947
                ]
            ]
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(current($data) == 'Webhook Handled');
    }

    /**
     * Failed Payment  routes access Test.
     *
     * @todo update this test , we are failing
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function FailPaymentPermittedRoutesAccess(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $apiException = null;
        $I->haveHttpHeader('Authorization', $userData->token);

        $user = Users::findFirst($userData->id);
        $subscription = Subscription::getByDefaultCompany($user);

        //Modify paid to 0
        $subscription->is_active = 0;
        $subscription->updateOrFail();

        //try a random route
        $apiException = false;
        try {
            $I->sendGet('/v1/custom-fields');
            $I->seeResponseIsSuccessful();
            $response = $I->grabResponse();
            $data = json_decode($response, true);
        } catch (UnauthorizedException $e) {
            $apiException = $e;
        }

        $subscription->is_active = 1;
        $subscription->updateOrFail();
        $I->assertTrue($apiException instanceof UnauthorizedException);
    }
}
