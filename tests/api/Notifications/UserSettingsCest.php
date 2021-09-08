<?php

namespace Canvas\Tests\api\Notifications;

use ApiTester;
use Canvas\Enums\Notification;
use Canvas\Models\NotificationType;
use Canvas\Models\Users;

class UserSettingsCest
{

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

        $I->haveHttpHeader('Authorization', $userData->token);

        $notificationType = NotificationType::findFirst();

        $I->sendPUT('/v1/users/' . $userData->id . '/notifications/' . $notificationType->getId(), []);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $user = Users::findFirst($userData->id);

        $I->assertFalse((bool)  $user->get(Notification::USER_MUTE_ALL_STATUS));
        $I->assertArrayHasKey('is_enabled', $data);
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
        $I->sendGet("/v1/users/{$userData->id}/notifications");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);


        $I->assertArrayHasKey('is_enabled', $data[0]);
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
        $notificationType = NotificationType::findFirst();

        $I->sendGet('/v1/users/' . $userData->id . '/notifications/' . $notificationType->getId());

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertArrayHasKey('is_enabled', $data);
    }

    /**
     * Get.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function muteAll(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);

        $I->sendDelete('/v1/users/' . $userData->id . '/notifications');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $user = Users::findFirst($userData->id);

        $I->assertTrue((bool)  $user->get(Notification::USER_MUTE_ALL_STATUS));
        $I->assertEquals('All Notifications are muted', $data);
    }
}
