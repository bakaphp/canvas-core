<?php

namespace Canvas\Tests\api;

use ApiTester;

class PermissionsResourcesCest extends BakaRestTest
{
    protected $model = 'permissions-resources';

    /**
     * Create
     *
     * @param ApiTester $I
     * @return void
     */
    public function create(ApiTester $I) : void
    {
    }

    /**
     * update
     *
     * @param ApiTester $I
     * @return void
     */
    public function update(ApiTester $I) : void
    {
    }

    /**
     * Get
     *
     * @param ApiTester $I
     * @return void
     */
    public function getById(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/permissions-resources');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendGet("/v1/{$this->model}/" . $data[0]['id'] . '?relationships=accesses');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data['id']));
        $I->assertTrue(isset($data['accesses'][0]['access_name']));
    }

    /**
     * List
     *
     * @param ApiTester $I
     * @return void
     */
    public function list(ApiTester $I):void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/permissions-resources?relationships=accesses');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data[0]['id']));
        $I->assertTrue(isset($data[0]['accesses'][0]['access_name']));
    }

    /**
     * Delete
     *
     * @param ApiTester $I
     * @return void
     */
    public function delete(ApiTester $I) : void
    {
    }
}
