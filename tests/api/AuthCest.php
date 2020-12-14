<?php

namespace Canvas\Tests\api;

use ApiTester;
use Canvas\Models\Users;
use Exception;
use function json_decode;
use Page\Data;

class AuthCest
{
    /**
     * Test login error.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function loginUnknownUser(ApiTester $I)
    {
        try {
            $I->sendPOST(
                Data::$loginUrl,
                [
                    'email' => 'user@example.com',
                    'password' => 'pass',
                ]
            );

            $response = $I->grabResponse();
            $response = json_decode($response);
        } catch (Exception $e) {
            $response = $e->getMessage();
        }

        $I->assertEquals('No User Found', $response);
    }

    /**
     * Create new users.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function signup(ApiTester $I)
    {
        $email = !Users::findFirstByEmail(Data::$defaultEmail) ? Data::$defaultEmail : $I->faker()->email;

        $I->sendPOST(Data::$usersUrl, [
            'email' => $email,
            'password' => Data::$defaultPassword,
            'verify_password' => Data::$defaultPassword,
            'firstname' => $I->faker()->firstName,
            'lastname' => $I->faker()->lastName,
            'displayname' => $I->faker()->userName,
            'default_company' => $I->faker()->domainWord,
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();

        $data = json_decode($response, true);

        $I->assertTrue(isset($data['user']['id']));
        $I->assertTrue(isset($data['user']['email']));
    }

    /**
     * Test login user.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function loginKnownUser(ApiTester $I)
    {
        $I->sendPOST(Data::$loginUrl, Data::loginJson());

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();

        $data = json_decode($response, true);
        $I->assertTrue(isset($data['id']));
        $I->assertTrue(isset($data['token']));
    }

    /**
     * Change user's email and relogin.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function changeUserEmail(ApiTester $I)
    {
        $userData = $I->apiLogin();
        $newEmail = 'tes3t@baka.io';

        //Get current user info
        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/users/' . $userData->id);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $user = json_decode($response, true);

        //Try to change user's email
        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/users/' . $user['user_activation_email'] . '/change-email', [
            'new_email' => $newEmail,
            'password' => Data::loginJson()['password']
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['id'] == $user['id']);

        // //Revert to old email
        $I->haveHttpHeader('Authorization', $data['token']);
        $I->sendPost('/v1/users/' . $user['user_activation_email'] . '/change-email', [
            'new_email' => 'tes2t@baka.io',
            'password' => Data::loginJson()['password']
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $newData = json_decode($response, true);

        $I->assertTrue($newData['id'] == $data['id']);
    }
}
