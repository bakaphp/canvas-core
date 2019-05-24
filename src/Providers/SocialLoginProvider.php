<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use function Canvas\Core\envValue;
use Hybridauth\Hybridauth;

class SocialLoginProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $config = $container->getShared('config');

        $container->setShared(
            'socialLogin',
            function () use ($config) {

                /**
                 * @todo Change the way provider information is handled
                 */
                $providers = [
                    'callback'=> 'https://apidev.kanvas.dev/v1/users/social',
                    'providers'=>[
                        'Facebook'=> ['enabled'=> true, 'keys'=>['id'=>$config->facebook->id,'secret'=>$config->facebook->secret]]
                    ]
                ];

                $hybridauth = new Hybridauth($providers);

                return $hybridauth;
            }
        );
    }
}
