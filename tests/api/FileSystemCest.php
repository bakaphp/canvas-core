<?php

namespace Canvas\Tests\api;

use ApiTester;
use function Baka\appPath;

class FileSystemCest
{
    protected $model = 'filesystem';

    /**
     * Get.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function getById(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet("/v1/{$this->model}");

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendGet("/v1/{$this->model}/" . $data[0]['id']);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue(isset($data['id']));
    }

    /**
     * Create.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function create(ApiTester $I) : void
    {
        $userData = $I->apiLogin();
        $fileName = 'test.png';

        $I->haveHttpHeader('Authorization', $userData->token);

        //use the app path, path changes by container
        $testFile = appPath() . 'tests/testfiles/test.png';

        $I->sendPost('/v1/' . $this->model, ['system_modules_id' => 1, 'entity_id' => 0], ['file' => $testFile]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data[0]['name'] == $fileName);
    }

    /**
     * update.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function update(ApiTester $I) : void
    {
        $userData = $I->apiLogin();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $this->model);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $newFileName = 'newfile2.png';
        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'name' => $newFileName,
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['name'] == $newFileName);
        //$I->assertTrue($data['entity_id'] == 12123);
    }
}
