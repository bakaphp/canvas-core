<?php

declare(strict_types=1);

namespace Canvas\Bootstrap;

use function Baka\appPath;
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
        $uri = rawurldecode($this->container->getRequest()->getServer('REQUEST_URI') ?? '');
        return $this->application->handle($uri);
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
