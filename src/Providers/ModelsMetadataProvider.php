<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Baka\Constants\Flags;
use function Baka\envValue;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\Model\Metadata\Memory as MemoryMetaDataAdapter;
use Phalcon\Mvc\Model\MetaData\Redis;
use Phalcon\Storage\SerializerFactory;

class ModelsMetadataProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $app = envValue('GEWAER_APP_ID', 1);

        $container->setShared(
            'modelsMetadata',
            function () use ($container, $app) {
                $config = $container->getShared('config');

                if (strtolower($config->app->env) != Flags::PRODUCTION) {
                    return new MemoryMetaDataAdapter();
                }

                //Connect to redis
                $cache = $config->get('cache')->toArray();
                $options = $cache['metadata']['prod']['options'];
                $options = [
                    'host' => $options['host'],
                    'port' => (int) $options['port'],
                    'lifetime' => (int) $options['lifetime'],
                    'prefix' => $options['prefix'],
                ];

                $serializerFactory = new SerializerFactory();
                $adapterFactory = new AdapterFactory($serializerFactory);

                return new Redis($adapterFactory, $options);
            }
        );
    }
}
