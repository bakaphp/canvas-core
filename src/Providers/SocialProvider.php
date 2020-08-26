<?php

namespace Canvas\Providers;

use function Canvas\Core\envValue;
use Facebook\Facebook;
use Google_Client;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Redis;

class SocialProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $container->setShared(
            'facebook',
            function (bool $prefix = true) use ($app) {
                //Connect to redis

                $fb = new Facebook([
                    'app_id' => envValue('FACEBOOK_APP_ID'),
                    'app_secret' => envValue('FACEBOOK_APP_SECRET'),
                    'default_graph_version' => 'v8.0',
                    // . . .
                ]);
                return $fb;
            }
        );

        $container->setShared(
            'google',
            function (bool $prefix = true) use ($app) {
                //Connect to redis
                $client = new Google_Client([
                    'client_id' => envValue('GOOGLE_CLIENT_ID')
                ]);
                return $client;
            }
        );
    }
}
