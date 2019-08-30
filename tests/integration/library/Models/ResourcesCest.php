<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Resources;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;
use Phalcon\Security\Random;

class ResourcesCest
{
    /**
     * is this name a resource?
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function isResource(IntegrationTester $I)
    {
        $I->assertTrue(gettype(Resources::isResource('Users')) == 'boolean');
    }

    /**
     * Get a resource by it name
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByName(IntegrationTester $I)
    {
        $I->assertTrue(Resources::getByName('Users') instanceof Resources);
    }
}