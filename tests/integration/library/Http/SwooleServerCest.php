<?php

namespace Gewaer\Tests\integration\library\Http;

use Canvas\Http\SwooleRequest;
use Canvas\Http\SwooleResponse;
use IntegrationTester;

class SwooleServerCest
{
    public function checkCanvasSwoolHttp(IntegrationTester $I)
    {
        $I->assertTrue(is_object(new SwooleRequest()));
        $I->assertTrue(is_object(new SwooleResponse()));
    }
}
