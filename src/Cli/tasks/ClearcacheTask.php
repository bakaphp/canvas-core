<?php

namespace Canvas\Cli\Tasks;

use function Canvas\Core\appPath;
use Phalcon\Cache\Backend\Libmemcached;
use Phalcon\Cli\Task as PhTask;
use Phalcon\Queue\Beanstalk\Extended as BeanstalkExtended;
use Phalcon\Queue\Beanstalk\Job;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class ClearcacheTask.
 *
 * @package Canvas\Cli\Tasks
 *
 * @property Libmemcached $cache
 * @property Config $config
 */
class ClearcacheTask extends PhTask
{
    /**
     * Clears the data cache from the application.
     */
    public function mainAction()
    {
        $this->clearFileCache();
        $this->clearMemCached();
    }

    /**
     * Clears file based cache.
     */
    protected function clearFileCache() : void
    {
        echo 'Clearing Cache folders' . PHP_EOL;

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

        echo PHP_EOL . 'Cleared Cache folders' . PHP_EOL;
    }

    /**
     * Clears memcached data cache.
     */
    protected function clearMemCached() : void
    {
        echo 'Clearing data cache' . PHP_EOL;

        $keys = $this->di->get('redis', [false])->keys('*' . $this->config->app->id . '*');

        echo sprintf('Found %s keys', count($keys)) . PHP_EOL;
        foreach ($keys as $key) {
            $this->redis->del($key);
        }

        echo  PHP_EOL . 'Cleared data cache' . PHP_EOL;
    }

    /**
     * Clean user session.
     *
     * @return void
     */
    public function sessionsAction() : void
    {
        //call queue
        $queue = new BeanstalkExtended([
            'host' => $this->config->beanstalk->host,
            'prefix' => $this->config->beanstalk->prefix,
        ]);

        //call que que tube
        $queue->addWorker(getenv('SESSION_QUEUE'), function (Job $job) {
            // Here we should collect the meta information, make the screenshots, convert the video to the FLV etc.

            $sessionId = $job->getBody();
            echo "\nProccessing:  {$sessionId}\n";

            $session = new \Baka\Auth\Models\Sessions();
            $session->clean($sessionId, true);

            // It's very important to send the right exit code!
            exit(0);
        });

        // Start processing queues
        $queue->doWork();
    }
}
