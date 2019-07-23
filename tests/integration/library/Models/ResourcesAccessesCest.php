<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\ResourcesAccesses;
use Canvas\Models\Resources;
use IntegrationTester;

class ResourcesAccessesCest
{
    /**
     * Set a setting for the given app
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function exist(IntegrationTester $I)
    {
        $resource = Resources::findFirst(1);
        $resourcesAccesses = ResourcesAccesses::exist($resource, 'create');
        $I->assertTrue(gettype($resourcesAccesses) == 'integer');
    }
}
