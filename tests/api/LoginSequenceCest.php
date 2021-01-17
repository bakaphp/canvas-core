<?php

namespace Canvas\Tests\api;

use ApiTester;

class LoginSequenceCest
{
    /**
     * Current App.
     */
    private $currentApp;

    /**
     * Current App.
     */
    private $defaultCompaniesId;

    /**
     * Get current user's info.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getCurrentUserInfo(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'users';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model . '/0');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $this->defaultCompaniesId = $data['default_company'];

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }

    /**
     * Validate user's photo relationship.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function validateUsersPhoto(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'users';
        $queryVariables = '/0?relationships=photo';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(array_key_exists('photo', $data));
    }

    /**
     * Validate user's roles relationship.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function validateUsersRoles(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'users';
        $queryVariables = '/0?relationships=roles';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(array_key_exists('roles', $data));
    }

    /**
     * Get user's companies info.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getUsersCompaniesInfo(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'companies';
        $queryVariables = '?relationships=apps,subscription,branch,branches,logo';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array');
    }

    /**
     * Get companies' branches.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getUsersCompaniesBranches(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'companies';
        $queryVariables = '?relationships=apps,subscription,branch,branches,logo';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(array_key_exists('branch', $data[0]) && $data[0]['branch']['companies_id'] == $data[0]['id']);
    }

    /**
     * Get companies'  branches.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getUsersCompaniesApps(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'companies';
        $queryVariables = '?relationships=apps,subscription,branch,branches,logo';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $this->currentApp = $data[0]['apps']['apps_id'];

        $I->assertTrue(array_key_exists('apps', $data[0]) && $data[0]['apps']['companies_id'] == $data[0]['id']);
    }

    /**
     * Get user's subscription.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getUsersDefaultSubscription(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'companies';
        $queryVariables = '?relationships=apps,subscription,branch,branches,logo';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(array_key_exists('subscription', $data[0]) && !empty($data[0]['subscription']['stripe_id']));
    }

    /**
     * Get system modules.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getSystemModules(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'system-modules';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }

    /**
     * Get App.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getApp(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'apps';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model . "/{$this->currentApp}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && $data['id'] == $this->currentApp);
    }

    /**
     * Get user's notifications.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getUsersNotifications(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $model = 'notifications';
        $queryVariables = '?format=true&q=(is_deleted:0)&sort=created_at%7CDESC';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $model . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data['data']) == 'array');
    }
}
