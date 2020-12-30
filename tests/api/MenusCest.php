<?php

namespace Canvas\Tests\api;

use ApiTester;
use Phalcon\Security\Random;

class MenusCest
{
    protected $model = 'menus';

    /**
     * Create.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function create(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $random = new Random();
        $menuName = $random->base58();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . $this->model, [
            'name' => $menuName,
            'slug' => $menuName
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $menuName);
    }

    /**
     * Get.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getBySlug(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendGet("/v1/{$this->model}/" . $data[0]['slug']);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data['name']));
        $I->assertTrue(isset($data['id']));
    }

    /**
     * update.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function update(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $random = new Random();
        $menuName = $random->base58();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $this->model);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'name' => $menuName
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $menuName);
    }
}
