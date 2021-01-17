<?php

namespace Canvas\Tests\api;

use ApiTester;

class SettingsAppsCest
{
    /**
     * Get custom fields modules.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getCustomFieldsModules(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'custom-fields-modules';
        $queryVariables = '?sort=&page=1&limit=25&format=true&q=(is_deleted:0)';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource . $queryVariables);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data['data']) == 'array' && !empty($data['data']));
    }

    /**
     * Get custom fields modules schema.
     *
     * @param ApiTester $I
     * @return void
     */
    public function getCustomFieldsModulesSchema(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $resource = 'schema/custom-fields-modules';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $resource);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(gettype($data['tableFields']) == 'array' && !empty($data['tableFields']));
    }
}
