<?php

declare(strict_types=1);
namespace Canvas\Providers;

use function Baka\envValue;
use Baka\Http\Exception\InternalServerErrorException;
use function Baka\isCLI;
use PDO;
use PDOException;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class DatabaseProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        /* $shared = true;
        //only when active do we run full async
        if (envValue('SWOOLE_COROUTINE_SHARED_DB', false)) {
            $shared = defined('API_TESTS') || !isCLI() ? true : false;
        } */

        $container->setShared(
            'db',
            function () {
                $options = [
                    'host' => envValue('DATA_API_MYSQL_HOST', 'localhost'),
                    'username' => envValue('DATA_API_MYSQL_USER', 'nanobox'),
                    'password' => envValue('DATA_API_MYSQL_PASS', ''),
                    'dbname' => envValue('DATA_API_MYSQL_NAME', 'gonano'),
                    'charset' => 'utf8mb4',
                    'options' => [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
                    ]
                ];

                try {
                    $connection = new Mysql($options);

                    // Set everything to UTF8
                    $connection->execute('SET NAMES utf8mb4', []);
                } catch (PDOException $e) {
                    throw new InternalServerErrorException($e->getMessage());
                }

                return $connection;
            }
        );
    }
}
