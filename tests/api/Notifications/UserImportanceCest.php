<?php

namespace Canvas\Tests\api\Notifications;

use ApiTester;
use Canvas\Models\Notifications\Importance;
use Canvas\Models\SystemModules;

class UserImportanceCest
{
    /**
     * update.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function create(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);

        $systemModule = SystemModules::findFirst();
        $systemNotificationImportance = Importance::findFirst();
        $entityId = 1;

        $I->sendPOST('/v1/users/' . $userData->id . '/notifications_importance', [
            'system_modules_id' => $systemModule->getId(),
            'entity_id' => $entityId,
            'importance_id' => $systemNotificationImportance->getId(),
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertArrayHasKey('importance_id', $data);
    }

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
        $I->sendGet('/v1/users/' . $userData->id . '/notifications_importance');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);


        $I->assertArrayHasKey('importance_id', $data[0]);
    }
}
