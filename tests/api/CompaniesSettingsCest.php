<?php

namespace Canvas\Tests\api;

use ApiTester;

class CompaniesSettingsCest
{
    /**
     * Get current Company's branches.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getCurrentCompaniesBranches(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'companies-branches';
        $queryVariables = '?sort=&page=1&limit=25&format=true&q=(is_deleted:0)';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data['data']) == 'array' && !empty($data['data']));
    }

    /**
     * Get all Companies Branches.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getCompaniesBranches(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'companies-branches';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }

    /**
     * Get current company's users.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getCurrentCompaniesUsers(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'users';
        $queryVariables = '?sort=&page=1&limit=25&format=true&relationships=roles';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }

    /**
     * Get active users schema.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getActiveUsers(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'schema/users-active';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data['tableFields']) == 'array' && !empty($data['tableFields']));
    }

    /**
     * Get current Company's roles.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getCurrentCompaniesRoles(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'roles';
        $queryVariables = '?sort=&page=1&limit=25&format=true&q=(is_deleted:0)';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data['data']) == 'array' && !empty($data['data']));
    }

    /**
     * Get roles schema.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getRolesSchema(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'schema/roles';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data['tableFields']) == 'array' && !empty($data['tableFields']));
    }

    /**
     * Get App Plans available.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getAppPlans(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'apps-plans';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data) == 'array' && !empty($data));
    }

    /**
     * Get App Plans settings relationship.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getAppPlansSettingsRelationship(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'apps-plans';
        $queryVariables = '?relationships=settings';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data[0]['settings']) == 'array' && !empty($data[0]['settings']));
    }
}
