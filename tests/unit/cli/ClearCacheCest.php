<?php

namespace Canvas\Tests\unit\cli;

use function Baka\appPath;
use Canvas\Cli\Tasks\ClearcacheTask;
use Canvas\Providers\AppProvider;
use Canvas\Providers\CacheDataProvider;
use Canvas\Providers\ConfigProvider;
use Canvas\Providers\DatabaseProvider;
use Canvas\Providers\ModelsCacheProvider;
use Canvas\Providers\RedisProvider;
use function fclose;
use FilesystemIterator;
use function iterator_count;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use Phalcon\Di\FactoryDefault\Cli;
use function uniqid;
use UnitTester;

class ClearCacheCest
{
    public function checkClearCache(UnitTester $I)
    {
        require appPath('vendor/autoload.php');

        $path = appPath('storage/cache/data/');
        $container = new Cli();
        $config = new ConfigProvider();
        $config->register($container);
        $appProvider = new AppProvider();
        $appProvider->register($container);
        $database = new DatabaseProvider();
        $database->register($container);
        $redis = new RedisProvider();
        $redis->register($container);
        $cache = new CacheDataProvider();
        $cache->register($container);
        $modelCache = new ModelsCacheProvider();
        $modelCache->register($container);
        $task = new ClearcacheTask();
        $task->setDI($container);

        $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
        $count = iterator_count($iterator);

        $this->createFile();
        $this->createFile();
        $this->createFile();
        $this->createFile();

        $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
        $I->assertEquals((int)$count + 4, (int)iterator_count($iterator));

        ob_start();
        $task->mainAction();
        $actual = ob_get_contents();
        ob_end_clean();

        $I->assertGreaterOrEquals(0, strpos($actual, 'Clearing Cache folders'));
        $I->assertGreaterOrEquals(0, strpos($actual, 'Cleared Cache folders'));

        $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);

        /**
         * @todo check the total # of files test generate
         */
        //$I->assertEquals(1, iterator_count($iterator));
    }

    private function createFile()
    {
        $name = appPath('storage/cache/data/') . uniqid('tmp_') . '.cache';
        $pointer = fopen($name, 'wb');
        fwrite($pointer, 'test');
        fclose($pointer);
    }
}
