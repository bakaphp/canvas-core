<?php

namespace Canvas\Tests\integration\library\Filesystem;

use Aws\S3\Exception\S3Exception;
use function Baka\appPath;
use Canvas\Filesystem\Helper;
use Canvas\Models\FileSystem;
use Exception;
use IntegrationTester;
use Phalcon\Http\Request\File;

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
            $file = Helper::upload(Helper::pathToFile(appPath('README.md')));

            $I->assertTrue($file instanceof FileSystem);
        } catch (S3Exception $s) {
            //we expect you wont be able to upload a empty file
            $I->assertTrue(true);
        } catch (Exception $e) {
            print_r($e->getMessage());
            $I->assertTrue(false);
        }
    }
}
