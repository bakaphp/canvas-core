<?php

namespace Canvas\Tests\api;

use ApiTester;

class CompaniesManagerCest
{
    /**
     * Get companies.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getCompanies(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'companies';
        $queryVariables = '?sort=&page=1&limit=25&format=true&relationships=hasActivities,logo&q=(is_deleted:0)';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data['data']) == 'array' && !empty($data['data']));
    }

    /**
     * Get companies schema.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getCompaniesSchema(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'schema/companies';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data['tableFields']) == 'array' && !empty($data['tableFields']));
    }
}
