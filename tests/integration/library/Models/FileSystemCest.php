<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\FileSystem;
use Canvas\Models\SystemModules;
use IntegrationTester;

class FileSystemCest
{
    /**
     * Get a filesystem entities from this system modules.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getAllByEntityId(IntegrationTester $I)
    {
        $systemModule = SystemModules::findFirst(1);
        $fileSystem = FileSystem::getAllByEntityId(1, $systemModule);
        $I->assertTrue(gettype($fileSystem) == 'object');
    }

    /**
     * Get the element by its entity id.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getById(IntegrationTester $I)
    {
        $fileSystem = FileSystem::getById(1);
        $I->assertTrue($fileSystem instanceof FileSystem);
    }

    /**
     * Get the element by its entity id.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function move(IntegrationTester $I)
    {
        $fileSystem = FileSystem::findFirst(1);
        $I->assertTrue(gettype($fileSystem->move('example')) == 'boolean');
    }
}
