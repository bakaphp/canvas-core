<?php

namespace Canvas\Providers;

use Baka\Mail\Manager as BakaMail;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class MailProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $container->setShared(
            'mail',
            function () use ($container) {
                $config = $container->getShared('config');

                $mailer = new BakaMail($config->email->toArray());
                return $mailer->createMessage();
            }
        );
    }
}
