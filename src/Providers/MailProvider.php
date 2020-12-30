<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Baka\Mail\Manager as BakaMail;

class MailProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->getShared('config');

        $container->setShared(
            'mail',
            function () use ($config) {
                $mailer = new BakaMail($config->email->toArray());
                return $mailer->createMessage();
            }
        );
    }
}
