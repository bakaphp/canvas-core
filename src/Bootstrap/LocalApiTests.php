<?php

namespace Canvas\Bootstrap;

use function Baka\appPath;
use Baka\Http\Request\Phalcon as Request;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Canvas\Http\Response;
use Throwable;

class LocalApiTests extends AbstractBootstrap
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
        $this->providers = require appPath('tests/providers-api.php');

        //run my parents setup
        parent::setup();
    }
}
