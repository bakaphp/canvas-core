<?php

namespace Canvas\Providers;

use Hybridauth\Hybridauth;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class SocialLoginProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared(
            'socialLogin',
            function () use ($container) {
                $config = $container->getShared('config');

                /**
                 * @todo Change the way provider information is handled
                 */
                $providers = [
                    'callback' => $config->social->callback,
                    'providers' => [
                        'Facebook' => [
                            'enabled' => true,
                            'keys' => ['id' => $config->social->facebook->id,
                                'secret' => $config->social->facebook->secret]
                        ]
                    ]
                ];

                $hybridauth = new Hybridauth($providers);

                return $hybridauth;
            }
        );
    }
}
