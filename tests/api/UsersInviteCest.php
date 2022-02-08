<?php

namespace Canvas\Tests\api;

use ApiTester;
use Canvas\Models\AppsPlans;
use Phalcon\Security\Random;

class UsersInviteCest
{
    /**
     * Insert and process a user invite for a non-existent user.
     *
     * @param ApiTester
     *
     * @return void
     */
    public function insertInvite(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $userName = $I->faker()->firstname;

        $testEmail = $userName . '@example.com';

        //reset
        AppsPlans::findFirst(1)->set('users_total', 30);

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/users/invite', [
            'email' => $testEmail,
            'role_id' => 1,
            'dont_send' => 1,
            'firstname' => 'testFirstsName',
            'lastname' => 'testLastName',
            'description' => 'testDescription',
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['email'] == $testEmail);

        $hash = $data['invite_hash'];

        $I->sendPost('/v1/users-invite/' . $hash, [
            'firstname' => 'testFirstsName',
            'lastname' => 'testLastName',
            'displayname' => $userName,
            'password' => 'testpassword',
            'user_active' => 1
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $dataInvite = json_decode($response, true);

        $I->assertTrue($dataInvite['user']['email'] == $testEmail);
    }

    /**
     * Get users invite by hash test.
     *
     * @param ApiTester
     *
     * @return void
     */
    public function getByHash(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $userName = $I->faker()->firstname;

        $testEmail = $userName . '@example.com';

        $I->haveHttpHeader('Authorization', $userData->token);

        //Insert a random new users invite
        $I->sendPost('/v1/users/invite', [
            'email' => $testEmail,
            'role_id' => 1,
            'dont_send' => 1,
            'firstname' => 'testFirstsName',
            'lastname' => 'testLastName',
            'description' => 'testDescription',
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['email'] == $testEmail);

        $hash = $data['invite_hash'];

        //Lets get the recently created users invite
        $I->sendGet('/v1/users-invite/validate/' . $hash);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['email'] == $testEmail);
    }

    /**
     * Resend invite test.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function resendInvite(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $companyName = $I->faker()->company;
        $resource = 'users-invite';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPOST('/v1/' . $resource . '/' . $data[count($data) - 1]['id'] . '/resend', []);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data == 'Success');
    }
}
