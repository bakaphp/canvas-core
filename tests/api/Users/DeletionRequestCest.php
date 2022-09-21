<?php

namespace Canvas\Tests\api\Users;

use ApiTester;
use Canvas\Models\Users;

class DeletionRequestCest
{
    /**
     * Get.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function requestDeletion(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost(
            '/v1/users/' . $userData->id . '/request-delete-account',
            [
                'data' => '',
                'request_date' => date('Y-m-d H:i:s'),
            ]
        );

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);
        $I->assertTrue(
            (bool) Users::findFirstOrFail($userData->id)->get('delete_requested')
        );
    }

    /**
     * Get.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function requestActivate(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendDelete(
            '/v1/users/' . $userData->id . '/request-delete-account',
        );

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertFalse(
            (bool) Users::findFirstOrFail($userData->id)->get('delete_requested')
        );
    }
}
