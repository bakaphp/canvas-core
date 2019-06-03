<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use AutoMapperPlus\AutoMapper;
use AutoMapperPlus\Configuration\AutoMapperConfig;

class MapperProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
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
