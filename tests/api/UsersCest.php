<?php

namespace Canvas\Tests\api;

use ApiTester;

class UsersCest
{
    /**
     * unsubscribe from notification
     *
     * @param ApiTester
     *
     * @return void
     */
    public function unsubscribe(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $userName = $I->faker()->firstname;

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/users/0/unsubscribe', [
            'notification_types' => [
                -1
            ]
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data[0]['notification_type_id']));
        $I->assertTrue($data[0]['notification_type_id'] == -1);
    }

    /**
     * unsubscribe from notification
     *
     * @param ApiTester
     *
     * @return void
     */
    public function getUsersByRole(ApiTester $I) : void
    {
        $role = 'admins';
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/users/' . $role . '/roles');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(!empty($data));
    }
}
