<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Elasticsearch\ClientBuilder;

class ElasticProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');

        $container->setShared(
            'elastic',
            function () use ($config) {
                $hosts = $config->elasticSearch->hosts->toArray();

                $client = ClientBuilder::create()
                                        ->setHosts($hosts)
                                        ->build();

                return $client;
            }
        );
    }
}
