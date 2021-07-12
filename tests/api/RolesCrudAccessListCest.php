<?php

namespace Canvas\Tests\api;

use ApiTester;
use Phalcon\Security\Random;

class RolesCrudAccessListCest extends BakaRestTest
{
    protected $model = 'roles-accesslist';

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
        $roleName = $random->base58();

        $role = [
            'roles' => [
                'description' => 'CI Test',
                'name' => $roleName
            ],
            'access' => [
                [
                    'access_name' => 'create',
                    'resources_name' => 'Users',
                    'allowed' => 0,
                ], [
                    'access_name' => 'update',
                    'resources_name' => 'Users',
                    'allowed' => 0,
                ],
            ]
        ];

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . $this->model, $role);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $roleName);
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
        $roleName = $random->base58();

        $role = [
            'roles' => [
                'description' => 'CI Test',
                'name' => $roleName
            ],
            'access' => [
                [
                    'access_name' => 'create',
                    'resources_name' => 'Users',
                    'allowed' => 0,
                ], [
                    'access_name' => 'update',
                    'resources_name' => 'Users',
                    'allowed' => 0,
                ],
            ]
        ];

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $this->model . '?q=(is_deleted:0)');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['roles_id'], $role);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $roleName);
    }

    /**
     * List.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function list(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/roles-acceslist?q=(is_deleted:0)');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data[0]['roles_id']));
    }

    /**
     * Get.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getById(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}?q=(is_deleted:0)");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendGet("/v1/{$this->model}/" . $data[0]['roles_id']);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data['roles_id']));
    }

    /**
     * Get.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function copy(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}?q=(is_deleted:0)");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $currentName = $data[0]['roles_name'];
        $I->sendPost("/v1/{$this->model}/" . $data[0]['roles_id'] . '/copy');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($currentName . 'Copied' == $data['name']);
    }

    /**
     * Delete.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function delete(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendDELETE("/v1/{$this->model}/" . $data[count($data) - 1]['roles_id']);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data[0] == 'Delete Successfully');
    }
}
