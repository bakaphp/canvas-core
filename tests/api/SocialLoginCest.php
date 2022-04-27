<?php

namespace Canvas\Tests\api;

use ApiTester;
use Phalcon\Security\Random;

class SocialLoginCest
{
    public $provider = 'facebook';

    public $testEmail;

    /**
     * Login a user that is not in the system and does not have a linked sourced from Facebook.
     *
     * @param ApiTester
     *
     * @return void
     */
    public function loginFirstTimeUser(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $random = new Random();
        $userName = $random->base58();

        $this->testEmail = $userName . '@example.com';

        // $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/users/social', [
            'provider' => $this->provider,
            'social_id' => 3434,
            'email' => $this->testEmail,
            'firstname' => 'ExampleFN',
            'lastname' => 'ExampleLN'
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->haveHttpHeader('Authorization', $userData->token);

        $I->sendGet('/v1/users/' . $data['id']);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $userCurrentData = json_decode($response, true);

        $I->assertTrue($userCurrentData['email'] == $this->testEmail);
    }

    /**
     * Login a user that is already in the system and has a linked sourced from Facebook.
     *
     * @param ApiTester
     *
     * @return void
     */
    public function loginLinkedUser(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        // $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/users/social', [
            'provider' => $this->provider,
            'social_id' => 3434,
            'email' => $this->testEmail,
            'firstname' => 'ExampleFN',
            'lastname' => 'ExampleLN'
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->haveHttpHeader('Authorization', $userData->token);

        $I->sendGet('/v1/users/' . $data['id']);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $userCurrentData = json_decode($response, true);

        $I->assertTrue($userCurrentData['email'] == $this->testEmail);
    }

    /**
     * Login a user that is already in the system and has a linked sourced from Facebook.
     *
     * @param ApiTester
     *
     * @return void
     */
    public function loginWithoutLink(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        // $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/users/social', [
            'provider' => $this->provider,
            'social_id' => 343,
            'email' => $this->testEmail,
            'firstname' => 'ExampleFN',
            'lastname' => 'ExampleLN'
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->haveHttpHeader('Authorization', $userData->token);

        $I->sendGet('/v1/users/' . $data['id']);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $userCurrentData = json_decode($response, true);

        $I->assertTrue($userCurrentData['email'] == $this->testEmail);
    }

    public function disconnectFromSocialSite(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        // $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendDelete('/v1/users/' . $userData->id . '/social/ ' . $this->provider);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertEquals('Disconnected from Social Site.', $data);
    }
}
