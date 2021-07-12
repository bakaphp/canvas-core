<?php

use Phalcon\Security\Random;

class EmailTemplatesVariablesCest
{
    /**
     * Model
     */
    protected $model = 'email-templates-variables';

    /**
     * Create a new Email Templates Variable
     *
     * @param ApiTester $I
     * @return void
     */
    public function insertTemplateVariable(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $random = new Random();
        $testName = 'test' . $random->base58();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . $this->model, [
            'companies_id' => 3,
            'apps_id' => 1,
            'system_modules_id' => 1,
            'users_id' => 2,
            'name' => $testName,
            'value' => $testName,
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        //Lets extract the main name of the created instance
        $baseName = substr($data['name'], 0, 12);

        $I->assertTrue($data['name'] == $testName);
    }

    /**
     * update a Email Templates Variable
     *
     * @param ApiTester $I
     * @return void
     */
    public function updateTemplateVariable(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $updatedName = 'Updated Test Value 2';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'value' => $updatedName
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['value'] == $updatedName);
    }
}
