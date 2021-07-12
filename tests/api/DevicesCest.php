<?php

namespace Canvas\Tests\api;

use ApiTester;
use Phalcon\Security\Random;

class DevicesCest
{
    /**
     * Test Device Id.
     */
    private $deviceId;

    /**
     * Create.
     *
     * @param ApiTester $I
     * @return void
     */
    public function attachDevice(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $random = new Random();
        $this->deviceId = $random->number(100);

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . 'users/' . $userData->id . '/devices', [
            'app' => 'baka',
            'deviceId' => $this->deviceId,
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['msg'] == 'User Device Associated');
    }

    /**
     * Create.
     *
     * @param ApiTester $I
     * @return void
     */
    public function deviceAlreadyAttached(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . 'users/' . $userData->id . '/devices', [
            'app' => 'baka',
            'deviceId' => $this->deviceId,
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['msg'] == 'User Device Associated');
    }

    /**
     * update.
     *
     * @param ApiTester $I
     * @return void
     */
    public function detachDevice(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendDelete('/v1/' . "users/{$userData->id}/devices/{$this->deviceId}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        unset($this->deviceId);
        $I->assertTrue($data['msg'] == 'User Device detached');
    }
}
