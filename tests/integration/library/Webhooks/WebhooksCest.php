<?php

namespace Gewaer\Tests\integration\library\Webhooks;

use Canvas\Models\UserWebhooks;
use Canvas\Models\Webhooks as ModelsWebhooks;
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

    public function run(IntegrationTester $I)
    {
        $userData = $I->grabFromDi('userData');
        $app = $I->grabFromDi('app');

        if (!$userWebhook = UserWebhooks::findFirstByCompaniesId($userData->getDefaultCompany()->getId())) {
            $userWebhook = new UserWebhooks();
            $userWebhook->webhooks_id = ModelsWebhooks::findFirstOrFail()->getId();
            $userWebhook->apps_id = $app->getId();
            $userWebhook->users_id = $userData->getId();
            $userWebhook->companies_id = $userData->getDefaultCompany()->getId();
            $userWebhook->url = 'http://localhost/v1';
            $userWebhook->method = 'POST';
            $userWebhook->format = 'JSON';
            $userWebhook->saveOrFail();
        }

        $results = Webhooks::run($userWebhook->getId(), ['test' => 'test']);
        $I->assertTrue(!empty($results));
    }

    /**
     * Verify the function to process a Webhook
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function process(IntegrationTester $I)
    {
        $userData = $I->grabFromDi('userData');
        $app = $I->grabFromDi('app');

        if (!$userWebhook = UserWebhooks::findFirstByCompaniesId($userData->getDefaultCompany()->getId())) {
            $userWebhook = new UserWebhooks();
            $userWebhook->webhooks_id = ModelsWebhooks::findFirstOrFail()->getId();
            $userWebhook->apps_id = $app->getId();
            $userWebhook->users_id = $userData->getId();
            $userWebhook->companies_id = $userData->getDefaultCompany()->getId();
            $userWebhook->url = 'http://localhost/v1';
            $userWebhook->method = 'POST';
            $userWebhook->format = 'JSON';
            $userWebhook->saveOrFail();
        }

        $results = Webhooks::process('Companies', ['test' => 'test'], 'create');
        $I->assertArrayHasKey('Companies', $results);
        $I->assertArrayHasKey('create', $results['Companies']);
        $I->assertTrue(is_array($results['Companies']['create'][0]));
    }
}
