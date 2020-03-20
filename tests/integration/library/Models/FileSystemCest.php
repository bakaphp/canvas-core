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
        $newFilesystem = new FileSystem();
        $newFilesystem->companies_id = $I->grabFromDi('userData')->currentCompanyId();
        $newFilesystem->apps_id = $I->grabFromDi('app')->getId();
        $newFilesystem->users_id = $I->grabFromDi('userData')->getId();
        $newFilesystem->name = 'test.png';
        $newFilesystem->path = '/test/test.png';
        $newFilesystem->url = 'http://kanvas.dev/test.png';
        $newFilesystem->size = '10';
        $newFilesystem->file_type = 'jpg';
        $newFilesystem->saveOrFail();

        $fileSystem = FileSystem::getById($newFilesystem->getId());
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
