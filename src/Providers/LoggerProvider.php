<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Baka\appPath;
use function Baka\envValue;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Raven_Client;
use Monolog\Handler\RavenHandler;

class LoggerProvider implements ServiceProviderInterface
{
    /**
     * Registers the logger component.
     *
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');

        $container->setShared(
            'log',
            function () use ($config) {
                /** @var string $logName */
                $logName = envValue('LOGGER_DEFAULT_FILENAME', 'api.log');
                /** @var string $logPath */
                $logPath = envValue('LOGGER_DEFAULT_PATH', 'storage/logs');
                $logFile = appPath($logPath) . '/' . $logName . '.log';

                $formatter = new LineFormatter("[%datetime%][%level_name%] %message% %context% \n");

                $logger = new Logger('api-logger');

                $handler = new StreamHandler($logFile, Logger::DEBUG);
                $handler->setFormatter($formatter);

                //only run logs in production
                if ($config->app->logsReport) {
                    //sentry logger
                    $client = new Raven_Client('https://' . getenv('SENTRY_RPOJECT_SECRET') . '@sentry.io/' . getenv('SENTRY_PROJECT_ID'));
                    $handlerSentry = new RavenHandler($client, Logger::ERROR);
                    $handlerSentry->setFormatter(new LineFormatter("%message% %context% %extra%\n"));
                    $logger->pushHandler($handlerSentry);
                }

                $logger->pushHandler($handler);

                return $logger;
            }
        );
    }
}
