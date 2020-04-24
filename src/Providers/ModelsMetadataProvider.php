<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Canvas\Core\envValue;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\Metadata\Memory as MemoryMetaDataAdapter;
use Phalcon\Mvc\Model\MetaData\Redis;
use Canvas\Constants\Flags;

class ModelsMetadataProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $config = $container->getShared('config');
        $app = envValue('GEWAER_APP_ID', 1);

        $container->setShared(
            'modelsMetadata',
            function () use ($config, $app) {
                if (strtolower($config->app->env) != Flags::PRODUCTION) {
                    return new MemoryMetaDataAdapter();
                }

                return new Redis(
                    [
                        'host' => envValue('REDIS_HOST', '127.0.0.1'),
                        'port' => (int) envValue('REDIS_PORT', 6379),
                        'prefix' => $app,
                        'persistent' => 0,
                        "statsKey"   => "_PHCM_MM",
                        'lifetime' => 172800,
                    ]
                );
            }
        );
    }
}
