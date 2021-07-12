<?php

namespace Canvas\Tests\integration\library\Http;

use Canvas\Http\Request;
use IntegrationTester;

class HttpCest
{
    protected $request;

    public function onConstruct()
    {
        $this->request = new Request();
    }

    public function checkCanvasRequest(IntegrationTester $I)
    {
        $I->assertTrue(is_object($this->request));
    }

    public function checkGetBearerTokenFromHeader(IntegrationTester $I)
    {
        $I->assertEmpty($this->request->getBearerTokenFromHeader());
    }

    public function checkIsEmptyBearerToken(IntegrationTester $I)
    {
        $I->assertTrue($this->request->isEmptyBearerToken());
    }
}
