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
        $config = $container->getShared('config');
        $app = $container->getShared('app');

        $container->setShared(
            'mail',
            function () use ($config, $app) {
                $emailConfig = $config->email->toArray();

                if ($fromEmail = $app->get('email_from_email')) {
                    $emailConfig['from']['email'] = $fromEmail;
                }

                if ($fromName = $app->get('email_from_name')) {
                    $emailConfig['from']['name'] = $fromName;
                }

                $mailer = new BakaMail($emailConfig);
                return $mailer->createMessage();
            }
        );
    }
}
