<?php

namespace Canvas\Tests\api\Users;

use ApiTester;
use Canvas\Models\Users;

class BranchCest
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

        $user = Users::findFirst($userData->id);
        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/companies-branches/{$user->getCurrentBranch()->getId()}/users");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);
        //$I->assertArrayHasKey('id', $data[0]);
    }
}
