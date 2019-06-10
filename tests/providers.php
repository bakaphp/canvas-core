<?php

/**
 * Enabled providers. Order does matter.
 */

use Canvas\Providers\CacheDataProvider;
use Canvas\Providers\ConfigProvider;
use Canvas\Providers\DatabaseProvider;
use Canvas\Providers\ErrorHandlerProvider;
use Canvas\Providers\LoggerProvider;
use Canvas\Providers\ModelsMetadataProvider;
use Canvas\Providers\RouterProvider;
use Canvas\Providers\SessionProvider;
use Canvas\Providers\QueueProvider;
use Canvas\Providers\MailProvider;
use Canvas\Providers\RedisProvider;
use Canvas\Providers\RequestProvider;
use Canvas\Providers\AclProvider;
use Canvas\Providers\AppProvider;
use Canvas\Providers\ResponseProvider;
use Canvas\Providers\FileSystemProvider;
use Canvas\Providers\EventManagerProvider;
use Canvas\Providers\MapperProvider;
use Canvas\Providers\ElasticProvider;

return [
    ConfigProvider::class,
    LoggerProvider::class,
    ErrorHandlerProvider::class,
    DatabaseProvider::class,
    ModelsMetadataProvider::class,
    RequestProvider::class,
    RouterProvider::class,
    CacheDataProvider::class,
    SessionProvider::class,
    QueueProvider::class,
    MailProvider::class,
    RedisProvider::class,
    AclProvider::class,
    AppProvider::class,
    ResponseProvider::class,
    FileSystemProvider::class,
    EventManagerProvider::class,
    MapperProvider::class,
    ElasticProvider::class
];
