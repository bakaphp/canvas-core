<?php

namespace Gewaer\Tests\integration\library\Webhooks;

use Canvas\Webhooks;
use IntegrationTester;

class WebhooksCest
{
    public function formatDataGet(IntegrationTester $I)
    {
        $data = Webhooks::formatData('GET', ['test' => 'test']);

        $I->assertTrue(isset($data['query']));
        $I->assertTrue($data['query']['test'] == 'test');
    }

    public function formatDataPost(IntegrationTester $I)
    {
        $data = Webhooks::formatData('POST', ['test' => 'test']);

        $I->assertTrue(isset($data['json']));
        $I->assertTrue(isset($data['form_params']));
        $I->assertTrue($data['json']['test'] == 'test');
        $I->assertTrue($data['form_params']['test'] == 'test');
    }

    public function formatDataPut(IntegrationTester $I)
    {
        $data = Webhooks::formatData('PUT', ['test' => 'test']);

        $I->assertTrue(isset($data['json']));
        $I->assertTrue(isset($data['form_params']));
        $I->assertTrue($data['json']['test'] == 'test');
        $I->assertTrue($data['form_params']['test'] == 'test');
    }

    public function formatDataPutWithHeader(IntegrationTester $I)
    {
        $data = Webhooks::formatData('PUT', ['test' => 'test'], ['Authorization' => 'token']);

        $I->assertTrue(isset($data['json']));
        $I->assertTrue(isset($data['form_params']));
        $I->assertTrue(isset($data['headers']));
        $I->assertTrue($data['json']['test'] == 'test');
        $I->assertTrue($data['form_params']['test'] == 'test');
        $I->assertTrue($data['headers']['Authorization'] == 'token');
    }

    public function formatDataPostWithHeader(IntegrationTester $I)
    {
        $data = Webhooks::formatData('POST', ['test' => 'test'], ['Authorization' => 'token']);

        $I->assertTrue(isset($data['json']));
        $I->assertTrue(isset($data['form_params']));
        $I->assertTrue(isset($data['headers']));
        $I->assertTrue($data['json']['test'] == 'test');
        $I->assertTrue($data['form_params']['test'] == 'test');
        $I->assertTrue($data['headers']['Authorization'] == 'token');
    }

    public function formatDataGettWithHeader(IntegrationTester $I)
    {
        $data = Webhooks::formatData('GET', ['test' => 'test'], ['Authorization' => 'token']);

        $I->assertTrue(isset($data['query']));
        $I->assertTrue($data['query']['test'] == 'test');
        $I->assertTrue($data['headers']['Authorization'] == 'token');
    }
}
