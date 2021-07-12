<?php

namespace Canvas\Tests\api;

use ApiTester;
use Phalcon\Security\Random;

class WebhooksCest extends BakaRestTest
{
    protected $model = 'webhooks';

    /**
     * Create
     *
     * @param ApiTester $I
     * @return void
     */
    public function create(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $random = new Random();
        $webhookName = 'test_' . $random->base58();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . $this->model, [
            'system_modules_id' => 1,
            'name' => $webhookName,
            'description' => $webhookName,
            'action' => 'create',
            'format' => 'JSON',
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $webhookName);
    }

    /**
     * update
     *
     * @param ApiTester $I
     * @return void
     */
    public function update(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $random = new Random();
        $webhookName = 'test_' . $random->base58();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $this->model);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'name' => $webhookName
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $webhookName);
    }
}
