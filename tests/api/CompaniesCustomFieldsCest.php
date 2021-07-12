<?php
use Canvas\Models\Users;

class CompaniesCustomFieldsCest
{
    /**
     * Model.
     */
    protected $model = 'companies-custom-fields';

    /**
     * Create a new Email Templates.
     *
     * @param ApiTester $I
     * @return void
     */
    public function insertCompaniesCustomField(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $testValue = 'test_' . time();
        $companyId = Users::findFirst($userData->id)->currentCompanyId();

        $I->haveHttpHeader('Authorization', $userData->token);

        $I->sendGet("/v1/custom-fields?q=(companies_id:$companyId)");
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);
        $customFieldId = $data[0]['id'];

        $I->sendPost('/v1/' . $this->model, [
            'companies_id' => $companyId,
            'custom_fields_id' => $customFieldId,
            'value' => $testValue
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['value'] == $testValue);
    }

    /**
     * update a Email Template.
     *
     * @param ApiTester $I
     * @return void
     */
    public function updateCompaniesCustomField(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $updatedValue = 'Updated Value';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'value' => $updatedValue
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['value'] == $updatedValue);
    }

    /**
     * Confirm custom field
     *
     * @param ApiTester $I
     * @return void
     */
    public function confirmCustomField(ApiTester $I): void
    {
        //Create a new company with a custom field
        $userData = $I->apiLogin();
        $testCompany = 'test_company' . time();
        $companyId = Users::findFirst($userData->id)->currentCompanyId();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/custom-fields?q=(companies_id:$companyId)");
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);
        $customFieldName = $data[0]['name'];

        $I->sendPut('/v1/' . 'companies/' . $companyId, [
            $customFieldName => 'example_custom_value'
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $companyData = json_decode($response, true);

        $customfield = $customFieldName;

        // Confirm newly created custom field
        $I->sendGet('/v1/custom-fields' . '?q=(name:' . $customfield . ')');

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $customFieldData = json_decode($response, true);

        /**
         * @todo  Check this assert
         */
        // $I->assertTrue(isset($data[$customFieldName]));
        $I->assertTrue($customfield == $customFieldData[0]['name']);
    }
}
