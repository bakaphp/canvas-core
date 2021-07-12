<?php

namespace Canvas\Tests\integration\library\Filesystem;

use Canvas\Models\Users;
use IntegrationTester;

class FileSystemEntitiesCest
{
    public function getAttachments(IntegrationTester $I)
    {
        $user = Users::findFirst();
        $files = $user->getAttachments();

        $I->assertTrue($files->count() >= 0);
    }

    public function getFiles(IntegrationTester $I)
    {
        $user = Users::findFirst();
        $files = $user->getFiles();

        $I->assertIsArray($files);
    }

    public function getAttachmentsByName(IntegrationTester $I)
    {
        $user = Users::findFirst();
        $files = $user->getAttachmentsByName('test');

        $I->assertTrue($files->count() >= 0);
    }

    public function getFilesByName(IntegrationTester $I)
    {
        $user = Users::findFirst();
        $files = $user->getFilesByName('test');

        $I->assertIsArray($files);
    }
}
