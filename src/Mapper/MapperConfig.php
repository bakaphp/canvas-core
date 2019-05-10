<?php
namespace Canvas\Mapper;

use AutoMapperPlus\Configuration\AutoMapperConfig;
use Phalcon\Di;
use Canvas\Models\Apps;
use Canvas\Mapper\DTO\DTOAppsSettings;

class MapperConfig
{
    public static function get()
    {
        $config = new AutoMapperConfig();
        $config->getOptions()->dontSkipConstructor();

        //DTOAppsSettings
        $config->registerMapping(Apps::class, DTOAppsSettings::class)
            ->forMember('settings', function (Apps $app) {
                return $app->settingsApp->toArray();
            });

        return $config;
    }
}
