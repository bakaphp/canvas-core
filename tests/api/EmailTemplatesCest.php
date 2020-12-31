<?php

class EmailTemplatesCest
{
    /**
     * Model.
     */
    protected $model = 'email-templates';

    /**
     * Create a new Email Templates.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function copyTemplate(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $testName = 'users-invite' . time();

        $I->haveHttpHeader('Authorization', $userData->token);

        //get the current list of emails
        $I->sendGet("/v1/{$this->model}?q=(apps_id:0)");
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);
        $copyId = $data[0]['id'];

        $I->sendPost('/v1/' . $this->model . '/' . $copyId . '/copy', [
            'name' => $testName,
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        //Lets extract the main name of the created instance
        $baseName = $data['name'];

        $I->assertTrue($baseName == $testName);
    }

    /**
     * update a Email Template.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function updateTemplate(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $updatedName = 'Updated Test Name 2';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'template' => $updatedName
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['template'] == $updatedName);
    }
}
