<?php

declare(strict_types=1);

namespace Canvas\Providers;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use AutoMapperPlus\AutoMapper;
use Canvas\Mapper\MapperConfig;

class MapperProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $container->set(
            'mapper',
            function () {
                return new AutoMapper(MapperConfig::get());
            }
        );
    }
}
