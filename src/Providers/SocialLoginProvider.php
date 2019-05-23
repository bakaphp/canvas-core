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
                    'callback'=> '',
                    'providers'=>[
                        'Facebook'=> ['enabled'=> true, 'keys'=>['id'=>'1197131063788181','secret'=>'1ee2138ace609549f524945ab9f3db3d']]
                    ]
                ];

                $hybridauth = new Hybridauth($providers);

                return $hybridauth;
            }
        );
    }
}
