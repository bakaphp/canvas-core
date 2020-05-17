<?php

declare(strict_types=1);

namespace Canvas\Bootstrap;

use function Canvas\Core\appPath;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;

/**
 * Class Api.
 *
 * @package Canvas\Bootstrap
 *
 * @property Micro $application
 */
class Swoole extends AbstractBootstrap
{
    /**
     * Run the application.
     *
     * @return mixed
     */
    public function run()
    {
        return $this->application->handle(
            $this->container->getRequest()->getServer('request_uri', null, '/')
        );
    }

    /**
     * @return mixed
     */
    public function setup()
    {
        //set the default DI
        $this->container = new FactoryDefault();
        //set all the services
        $this->providers = require appPath('api/config/providers.php');

        //run my parents setup
        parent::setup();
    }
}
