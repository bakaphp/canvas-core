<?php

namespace Canvas\Tests\api;

use ApiTester;

class SourcesCest
{
    protected $model = 'sources';

    /**
     * List all sources
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function list(ApiTester $I): void
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
        $I->sendGet("/v1/{$this->model}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendGet("/v1/{$this->model}/" . $data[0]['id']);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data['id']));
    }
}
