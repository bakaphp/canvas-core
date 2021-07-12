<?php

namespace Canvas\Tests\api;

use ApiTester;

class UserSettingsCest
{
    /**
     * Get languages.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getLanguages(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'languages';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }

    /**
     * Get timezones.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getTimezones(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'timezones';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }

    /**
     * Get locales.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getLocales(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'locales';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource . '?limit=300');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }

    /**
     * Get currencies.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getCurrencies(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'currencies';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource . '?limit=200');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }

    /**
     * Get all roles.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getRoles(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'roles';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }
}
