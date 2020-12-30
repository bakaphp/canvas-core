<?php

namespace Canvas\Tests\api;

use ApiTester;

class PaymentsCest
{
    protected $model = 'payments';

    /**
     * Get Stripe Signature
     * @return string
     */
    public static function getStripeSignature(): string 
    {
        return 't=1492774577,v1=5257a869e7ecebeda32affa62cdca3fa51cad7e77a0e56ff536d0ce8e108d8bd,v0=6ffbb59b2300aae63f272406069a9788598b792a944a07aba816edb039989a39';
    }

    /**
     * Pending Payment
     *
     * @param ApiTester $I
     * @return void
     */
    public function pendingPayment(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->haveHttpHeader('Stripe-Signature', self::getStripeSignature());
        $I->sendPost('/v1/' . 'webhook/' . $this->model, [
            'type' => 'charge.pending',
            'data' => [
                'object' => [
                    'customer' => $userData->stripe_id,
                    'created' => time()
                ]
            ]
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(current($data) == 'Webhook Handled');
    }

    /**
     * Failed Payment
     *
     * @param ApiTester $I
     * @return void
     */
    public function failedPayment(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->haveHttpHeader('Stripe-Signature', self::getStripeSignature());
        $I->sendPost('/v1/' . 'webhook/' . $this->model, [
            'type' => 'charge.failed',
            'data' => [
                'object' => [
                    'customer' => $userData->stripe_id,
                    'created' => time()
                ]
            ]
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(current($data) == 'Webhook Handled');
    }

    /**
     * Successful Payment
     *
     * @param ApiTester $I
     * @return void
     */
    public function SucceededPayment(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->haveHttpHeader('Stripe-Signature', self::getStripeSignature());
        $I->sendPost('/v1/' . 'webhook/' . $this->model, [
            'type' => 'charge.succeeded',
            'data' => [
                'object' => [
                    'customer' => $userData->stripe_id,
                    'created' => time()
                ]
            ]
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(current($data) == 'Webhook Handled');
    }
}
