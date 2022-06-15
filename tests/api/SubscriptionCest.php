<?php

use Canvas\Models\Subscription;

class SubscriptionCest
{
    protected $model = 'subscriptions';

    /**
     * List all sources.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function list(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);
        $I->assertTrue(!empty($data));
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
        $I->sendGet("/v1/{$this->model}");
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $subscription = $data[0];

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendDelete("/v1/{$this->model}/" . $subscription['id']);

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
    public function reactivateSubscription(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $subscription = $data[0];

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost("/v1/{$this->model}/" . $subscription['id'] . '/reactivate');

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
    public function swap(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $subscription = $data[0];

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPut("/v1/{$this->model}/" . $subscription['id'], [
            'stripe_id' => 'monthly-10-2',
        ]);

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
    public function updatePayment(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $subscription = $data[0];

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPut("/v1/{$this->model}/" . $subscription['id'] . '/payment-method', [
            'card_number' => '4242424242424242',
            'card_exp_month' => '12',
            'card_exp_year' => date('Y') + 1,
            'card_cvc' => 333,
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data['id']));
    }

    /**
     * get subscription payment info.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getPaymentInfo(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $subscription = $data[0];

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}/" . $subscription['id'] . '/payment-method');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data['last4']));
    }
}
