<?php

declare(strict_types=1);

namespace Canvas\Providers;

use AutoMapperPlus\AutoMapper;
use AutoMapperPlus\Configuration\AutoMapperConfig;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class MapperProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        //configure the dto config
        $container->setShared(
            'dtoConfig',
            function () {
                $config = new AutoMapperConfig();
                $config->getOptions()->dontSkipConstructor();

                return $config;
            }
        );

        //configure the dto mapper
        $container->set(
            'mapper',
            function () use ($container) {
                return new AutoMapper($container->get('dtoConfig'));
            }
        );
    }
}
