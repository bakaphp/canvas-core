<?php

namespace Canvas\Bootstrap;

use function Baka\appPath;
use Phalcon\Di\FactoryDefault;

class IntegrationTests extends Api
{
    /**
     * Run the application.
     *
     * @return mixed
     */
    public function run()
    {
        return $this->application;
    }

    /**
     * @return mixed
     */
    public function setup()
    {
        //set the default DI
        $this->container = new FactoryDefault();
        //set all the services

        /**
        * @todo Find a better way to handle unit test file include
        */
        $this->providers = require appPath('tests/providers.php');

        //run my parents setup
        $this->container->set('metrics', microtime(true));
        $this->setupApplication();
        $this->registerServices();
    }
}
