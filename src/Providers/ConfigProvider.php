<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Canvas\Core\appPath;
use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Config;

class ConfigProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $container->setShared(
            'config',
            function () {

                /**
                 * @todo Find a better way to handle unit test file include
                 */
                $data = require appPath('src/Core/config.php');

                return new Config($data);
            }
        );
    }
}
