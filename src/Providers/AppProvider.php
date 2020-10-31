<?php

namespace Canvas\Providers;

use function Baka\envValue;
use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Http\Request;
use Canvas\Models\Apps;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class AppProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');

        $container->setShared(
            'app',
            function () use ($config) {
                $request = new Request();
                //$appKey = $request->hasHeader('KanvasKey') ? $request->getHeader('KanvasKey') : $config->app->id;

                $domainBasedApp = (bool) envValue('KANVAS_CORE_DOMAIN_BASED_APP', false);
                $domainName = $request->getHttpHost();
                $appKey = $config->app->id;

                $app = !$domainBasedApp ? Apps::findFirstByKey($appKey) : Apps::getByDomainName($domainName);

                if (!$app) {
                    $msg = !$domainBasedApp ? 'No App configure with this key ' . $appKey : 'No App configure by this domain ' . $domainName;
                    throw new InternalServerErrorException($msg);
                }
                return $app;
            }
        );
    }
}
