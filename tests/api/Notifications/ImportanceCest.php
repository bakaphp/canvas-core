<?php

namespace Canvas\Tests\api\Notifications;

use ApiTester;

class ImportanceCest
{


    /**
     * Get.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function list(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/notifications_importance');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);


        $I->assertArrayHasKey('id', $data[0]);
    }
}
