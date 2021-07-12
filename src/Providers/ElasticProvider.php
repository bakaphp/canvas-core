<?php

namespace Canvas\Providers;

use Elasticsearch\ClientBuilder;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class ElasticProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared(
            'elastic',
            function () use ($container) {
                $config = $container->getShared('config');

                $hosts = $config->elasticSearch->hosts->toArray();

                $client = ClientBuilder::create()
                                        ->setHosts($hosts)
                                        ->build();

                return $client;
            }
        );
    }
}
