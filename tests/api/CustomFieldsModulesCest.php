<?php
use Canvas\Models\Companies;

class CustomFieldsModulesCest
{
    /**
     * Model
     */
    protected $model = 'custom-fields-modules';

    /**
     * Create a new Email Templates
     *
     * @param ApiTester $I
     * @return void
     */
    public function insertModule(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $testName = 'test_' . time();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . $this->model, [
            'apps_id' => 1,
            'name' => $testName,
            'model_name' => get_class(new Companies())
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $testName);
    }

    /**
     * update a Email Template
     *
     * @param ApiTester $I
     * @return void
     */
    public function updateModule(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $updatedName = 'Updated Name';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'name' => $updatedName,
            'model_name' => get_class(new Companies())
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $updatedName);
    }

    /**
     * Fetch all custom fields of a module by its id
     *
     * @param ApiTester $I
     * @return void
     */
    public function listCustomFieldsByModuleId(ApiTester $I): void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}" . '/1/fields');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertNotEmpty($data, 'Response not empty');
    }
}
