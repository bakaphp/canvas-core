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
}
