<?php

class CustomFiltersCest
{
    /**
     * Model.
     */
    protected $model = 'custom-filters';
    protected array $record = [];
    /**
     * Create a new Email Templates.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function create(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $testName = 'filter';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . $this->model, [
            'system_modules_id' => 2,
            'fields_type_id' => 1,
            'apps_id' => 1,
            'name' => $testName,
            'sequence_logic' => '1 AND 2',
            'description' => 'Criteria example',
            'criterias' => [
                [
                    'comparator' => '>',
                    'value' => '1',
                    'field' => 'id'
                ],
                'and',
                [
                    'comparator' => '>',
                    'field' => 'created_at',
                    'value' => '2019-01-01'
                ]
            ]
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $testName);
    }

    public function execute(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $testName = 'filter';

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $this->model);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);


        $I->sendPost('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], []);
        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertIsArray($data);
        $I->assertTrue(count($data) > 0);
    }

    public function update(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $testName = 'filter' . time();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $this->model);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);


        $I->sendPut('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'system_modules_id' => 2,
            'fields_type_id' => 1,
            'apps_id' => 1,
            'name' => $testName,
            'sequence_logic' => '1 AND 2',
            'description' => 'Criteria example',
            'criterias' => [
                [
                    'comparator' => '>',
                    'value' => '1',
                    'field' => 'id'
                ],
                'and',
                [
                    'comparator' => '>',
                    'field' => 'created_at',
                    'value' => '2019-01-01'
                ]
            ]
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $testName);
    }

    public function list(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $testName = 'filter' . time();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $this->model);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);


        $I->assertIsArray($data);
        $I->assertTrue(count($data) > 0);
    }

    public function delete(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $testName = 'filter' . time();

        $I->haveHttpHeader('Authorization', $userData->token);

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $this->model);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);


        $I->sendDelete('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], []);


        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);
        $I->assertEquals('Delete Successfully', $data[0]);
    }
}
