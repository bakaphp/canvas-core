<?php

namespace Canvas\Cli\Tasks;

use function Baka\appPath;
use Dotenv\Dotenv;
use Phalcon\Cli\Task as PhTask;

class CacheTask extends PhTask
{
    /**
     * Clears the data cache from the application.
     */
    public function mainAction() : void
    {
    }

    /**
     * Set up the .env environment in production.
     *
     * @return void
     */
    public function dotenvAction()
    {
        $dotenv = Dotenv::createImmutable(appPath());
        $dotenv->load();

        echo 'Copy and past this .env variables to your www.conf on PHP-FPM' . PHP_EOL . PHP_EOL;

        foreach ($_ENV as $key => $value) {
            if (!empty($value)) {
                echo "env[{$key}] = '{$value}'" . PHP_EOL;
            }
        }
    }
}
