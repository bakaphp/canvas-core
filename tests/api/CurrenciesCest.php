<?php

namespace Canvas\Tests\api;

use ApiTester;
use Phalcon\Security\Random;

class CurrenciesCest extends BakaRestTest
{
    protected $model = 'currencies';

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
        $random = new Random();
        $CountryName = $random->base58();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendPost('/v1/' . $this->model, [
            'country' => $CountryName
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['country'] == $CountryName);
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
        $random = new Random();
        $CountryName = $random->base58();

        $I->haveHttpHeader('Authorization', $userData->token);
        $I->sendGet('/v1/' . $this->model);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->sendPUT('/v1/' . $this->model . '/' . $data[count($data) - 1]['id'], [
            'country' => $CountryName
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['country'] == $CountryName);
    }
}
