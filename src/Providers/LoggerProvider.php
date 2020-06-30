<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Canvas\Core\appPath;
use function Canvas\Core\envValue;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Sentry\ClientBuilder;
use Sentry\Monolog\Handler as SentryHandler;
use Sentry\State\Hub;

class LoggerProvider implements ServiceProviderInterface
{
    /**
     * Registers the logger component.
     *
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
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
                if ($config->app->production) {
                    //sentry logger
                    $client = ClientBuilder::create([
                        'dsn' => 'https://' . getenv('SENTRY_RPOJECT_SECRET') . '@sentry.io/' . getenv('SENTRY_PROJECT_ID')
                    ])->getClient();

                    $hub = Hub::setCurrent(new Hub($client));

                    $handlerSentry = new SentryHandler($hub, Logger::ERROR);
                    $handlerSentry->setFormatter(new LineFormatter("%message% %context% %extra%\n"));
                    $logger->pushHandler($handlerSentry);
                }

                $logger->pushHandler($handler);

                return $logger;
            }
        );
    }
}
