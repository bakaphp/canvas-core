<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\FileSystemEntities;
use Canvas\Models\SystemModules;
use IntegrationTester;

class FileSystemEntitiesCest
{
    /**
     * Get a filesystem entities from this system modules.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByIdWithSystemModule(IntegrationTester $I)
    {
        $systemModule = SystemModules::findFirst(1);
        $fileSystemEntities = FileSystemEntities::getByIdWithSystemModule(1, $systemModule);
        $I->assertTrue($fileSystemEntities instanceof FileSystemEntities);
    }

    /**
     * Get a filesystem entities from this system modules.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getById(IntegrationTester $I)
    {
        $fileSystemEntities = FileSystemEntities::getById(1);
        $I->assertTrue($fileSystemEntities instanceof FileSystemEntities);
    }

    /**
     * Get a filesystem entities from this system modules.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getByEntityId(IntegrationTester $I)
    {
        $fileSystemEntities = FileSystemEntities::getByEntityId($I->grabFromDi('userData')->getDefaultCompany()->getId());
        $I->assertTrue($fileSystemEntities instanceof FileSystemEntities);
    }

    /**
     * Given a entity id get all its asociated files.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getAllByEntityId(IntegrationTester $I)
    {
        $fileSystemEntities = FileSystemEntities::getByEntityId($I->grabFromDi('userData')->getDefaultCompany()->getId());
        $I->assertTrue($fileSystemEntities instanceof FileSystemEntities);
    }
}
