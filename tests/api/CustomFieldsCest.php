<?php

class CustomFieldsCest
{
    /**
     * Model.
     */
    protected $model = 'custom-fields';

    /**
     * Create a new Email Templates.
     *
     * @param ApiTester $I
     * @return void
     */
    public function insertCustomField(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $testName = 'test_' . time();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . $this->model, [
            'users_id' => 3,
            'companies_id' => 1,
            'apps_id' => 1,
            'name' => $testName,
            'label' => $testName,
            'custom_fields_modules_id' => 1,
            'fields_type_id' => 1
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $testName);
    }

    /**
     * update a Email Template.
     *
     * @param ApiTester $I
     * @return void
     */
    public function updateCustomField(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $updatedName = 'Updated Name';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'name' => $updatedName
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $updatedName);
    }
}
