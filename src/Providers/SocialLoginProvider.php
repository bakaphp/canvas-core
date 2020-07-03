<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use function Baka\envValue;
use Hybridauth\Hybridauth;

class SocialLoginProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');

        $container->setShared(
            'socialLogin',
            function () use ($config) {

                /**
                 * @todo Change the way provider information is handled
                 */
                $providers = [
                    'callback'=> $config->social->callback,
                    'providers'=>[
                        'Facebook'=> [
                            'enabled'=> true,
                            'keys'=>['id'=>$config->social->facebook->id,
                            'secret'=>$config->social->facebook->secret]
                        ]
                    ]
                ];

                $hybridauth = new Hybridauth($providers);

                return $hybridauth;
            }
        );
    }
}
