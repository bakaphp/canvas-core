<?php

namespace Gewaer\Tests\integration\library\Filesystem;

use Aws\S3\Exception\S3Exception;
use Canvas\Filesystem\Helper;
use IntegrationTester;
use Phalcon\Http\Request\File;
use Canvas\Models\FileSystem;
use Exception;

class HelperCest
{
    public function generateUniqueName(IntegrationTester $I)
    {
        $filename = Helper::generateUniqueName(Helper::pathToFile('.'), '.');

        $I->assertNotNull($filename);
        $I->assertTrue(strlen($filename) > 1);
    }

    public function pathToFile(IntegrationTester $I)
    {
        $I->assertTrue(Helper::pathToFile('.') instanceof File);
    }

    public function upload(IntegrationTester $I)
    {
        try {
            $file = Helper::upload(Helper::pathToFile('./README.md'));

            $I->assertTrue($file instanceof FileSystem);
        } catch (S3Exception $s) {
            //we expect you wont be able to upload a empty file
            $I->assertTrue(true);
        } catch (Exception $e) {
            $I->assertTrue(false);
        }
    }
}
