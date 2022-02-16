<?php

declare(strict_types=1);

namespace Canvas\Bootstrap;

use function Baka\appPath;
// use Baka\Http\Request\Phalcon as Request;
use Illuminate\Http\Request;
use Canvas\Http\Response;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Throwable;
use Illuminate\Contracts\Http\Kernel;

/**
 * Class Api.
 *
 * @package Canvas\Bootstrap
 *
 * @property Micro $application
 */
class Api extends AbstractBootstrap
{
    /**
     * Run the application.
     *
     * @return mixed
     */
    public function run()
    {
        try {
            $request = Request::capture();
            // $uri = rawurldecode($request->getServer('REQUEST_URI') ?? '');
            // $uri = $request->url();
            return $this->application->dispatch($request);
        } catch (Throwable $e) {
            $response = new Response();
            $response->handleException($e)->send();
        }
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
        $this->providers = require appPath('api/config/providers.php');

        //run my parents setup
        parent::setup();
    }
}
