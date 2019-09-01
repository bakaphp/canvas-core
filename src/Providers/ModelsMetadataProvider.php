<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Canvas\Core\envValue;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\MetaData\Libmemcached;
use Phalcon\Mvc\Model\Metadata\Memory as MemoryMetaDataAdapter;
use Canvas\Constants\Flags;

class ModelsMetadataProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $config = $container->getShared('config');

        $container->setShared(
            'modelsMetadata',
            function () use ($config, $container) {
                if (strtolower($config->app->env) != Flags::PRODUCTION) {
                    return new MemoryMetaDataAdapter();
                }

                $app = $container->getShared('app');

                $prefix = 'metadata';
                $backOptions = [
                    'servers' => [
                        0 => [
                            'host' => envValue('DATA_API_MEMCACHED_HOST', '127.0.0.1'),
                            'port' => envValue('DATA_API_MEMCACHED_PORT', 11211),
                            'weight' => envValue('DATA_API_MEMCACHED_WEIGHT', 100),
                        ],
                    ],
                    'client' => [
                        \Memcached::OPT_HASH => \Memcached::HASH_MD5,
                        \Memcached::OPT_PREFIX_KEY => $app->name.'-',
                    ],
                    'lifetime' => 3600,
                    'prefix' => $prefix . '-',
                ];

                return new Libmemcached($backOptions);
            }
        );
    }
}
