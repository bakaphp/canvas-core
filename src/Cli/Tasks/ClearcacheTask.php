<?php

namespace Canvas\Cli\Tasks;

use function Baka\appPath;
use Canvas\Contracts\Models\CacheKeys;
use Phalcon\Cli\Task as PhTask;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ClearcacheTask extends PhTask
{
    use CacheKeys;

    /**
     * Clears the data cache from the application.
     */
    public function mainAction() : void
    {
        $this->clearFileCache();
        $this->clearModelMetaDataRedisCache();
    }

    /**
     * Delete all model cache.
     *
     * @return void
     */
    public function modelAction() : void
    {
        $this->clearModelRedisCache();
    }

    /**
     * Delete all model cache.
     *
     * @return void
     */
    public function modelMetaDataAction() : void
    {
        $this->clearModelMetaDataRedisCache();
    }

    /**
     * Clear all data from the application.
     *
     * @return void
     */
    public function allAction() : void
    {
        $this->mainAction();
        $this->clearRedisCache();
    }

    /**
     * Clears file based cache.
     */
    protected function clearFileCache() : void
    {
        echo PHP_EOL . 'Clearing Cache folders' . PHP_EOL;

        $fileList = [];
        $whitelist = ['.', '..', '.gitignore'];
        $path = appPath('storage/cache');
        $dirIterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator(
            $dirIterator,
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /**
         * Get how many files we have there and where they are.
         */
        foreach ($iterator as $file) {
            if (true !== $file->isDir() && true !== in_array($file->getFilename(), $whitelist)) {
                $fileList[] = $file->getPathname();
            }
        }

        echo sprintf('Found %s files', count($fileList)) . PHP_EOL;
        foreach ($fileList as $file) {
            echo '.';
            unlink($file);
        }

        echo  'Cleared Cache folders' . PHP_EOL;
    }

    /**
     * Clears memcached data cache.
     */
    protected function clearRedisCache() : void
    {
        echo 'Are you sure you want delete your FULL REDIS DB?  [y/N]' . PHP_EOL;

        //read user input
        $confirmation = trim(fgets(STDIN));
        if ($confirmation !== 'y') {
            // The user did not say 'y'.
            return;
        }

        echo PHP_EOL . 'Clearing data cache' . PHP_EOL;

        $keys = $this->di->get('redis', [false])->keys('*');
        echo sprintf('Found %s keys', count($keys)) . PHP_EOL;
        foreach ($keys as $key) {
            $this->redis->del($key);
        }

        echo   'Cleared data cache' . PHP_EOL;
    }

    /**
     * Clear all model metadata cache.
     *
     * @return void
     */
    protected function clearModelMetaDataRedisCache() : void
    {
        echo PHP_EOL . 'Clearing Model data cache' . PHP_EOL;

        $cache = $this->di->get('config')->get('cache')->toArray();
        $options = $cache['metadata']['prod']['options'];

        $keys = $this->di->get('redisUnSerialize', [false])->keys($options['prefix'] . '*');

        echo sprintf('Found %s Models Schema Cache', count($keys)) . PHP_EOL;

        //delete each metada from redis
        foreach ($keys as $key) {
            $this->redisUnSerialize->del($key);
        }
        
        echo  'Cleared Model schema cache' . PHP_EOL;
    }

    /**
     * Clear all model cache.
     *
     * @return void
     */
    protected function clearModelRedisCache() : void
    {
        echo 'Are you sure you want delete all your Model Cache?  [y/N]' . PHP_EOL;

        //read user input
        $confirmation = trim(fgets(STDIN));
        if ($confirmation !== 'y') {
            // The user did not say 'y'.
            return;
        }

        echo sprintf('Found %s Models Cache', $this->clearCacheByKeyPattern('')) . PHP_EOL;

        echo  'Cleared Model schema cache' . PHP_EOL;
    }

    /**
     * Clean user session.
     *
     * @deprecated version 1
     *
     * @return void
     */
    public function sessionsAction() : void
    {
    }
}
