<?php

declare(strict_types=1);

namespace Canvas\Bootstrap;

use function Canvas\Core\appPath;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Canvas\Http\Response;
use Phalcon\Http\Request;
use Throwable;
use Canvas\Exception\ServerErrorHttpException;
use Canvas\Constants\Flags;

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
            return $this->application->handle();
        } catch (Throwable $e) {
            $this->handleException($e)->send();
        }
    }

    /**
     * Handle the exception we throw from our api.
     *
     * @param Throwable $e
     * @return Response
     */
    public function handleException(Throwable $e): Response
    {
        $response = new Response();
        $request = new Request();
        $identifier = $request->getServerAddress();
        $config = $this->container->getConfig();

        $httpCode = (method_exists($e, 'getHttpCode')) ? $e->getHttpCode() : 404;
        $httpMessage = (method_exists($e, 'getHttpMessage')) ? $e->getHttpMessage() : 'Not Found';
        $data = (method_exists($e, 'getData')) ? $e->getData() : [];

        $response->setHeader('Access-Control-Allow-Origin', '*'); //@todo check why this fails on nginx
        $response->setStatusCode($httpCode, $httpMessage);
        $response->setContentType('application/json');
        $response->setJsonContent([
            'errors' => [
                'type' => $httpMessage,
                'identifier' => $identifier,
                'message' => $e->getMessage(),
                'trace' => strtolower($config->app->env) != Flags::PRODUCTION ? $e->getTraceAsString() : null,
                'data' => $data,
            ],
        ]);

        //only log when server error production is seerver error or dev
        if ($e instanceof ServerErrorHttpException || strtolower($config->app->env) != Flags::PRODUCTION) {
            $this->container->getLog()->error($e->getMessage(), [$e->getTraceAsString()]);
        }

        return $response;
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
