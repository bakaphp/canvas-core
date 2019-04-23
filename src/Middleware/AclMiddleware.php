<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Canvas\Exception\ServerErrorHttpException;
use Canvas\Exception\PermissionException;
use Canvas\Models\Subscription;

/**
 * Class AclMiddleware.
 *
 * @package Canvas\Middleware
 */
class AclMiddleware extends TokenBase
{
    /**
     * Call me.
     *
     * @param Micro $api
     * @todo need to check section for auth here
     * @return bool
     */
    public function call(Micro $api)
    {
        $router = $api->getService('router');
        $request = $api->getService('request');

        if ($this->isValidCheck($request, $api)) {
            // explode() by / , postiion #1 is always the controller , so its the resource ^.^
            $matchRouter = explode('/', $router->getMatchedRoute()->getCompiledPattern());

            $resource = ucfirst(isset($matchRouter[2]) ? $matchRouter[2] : $matchRouter[1]); //2 is alwasy the controller of the router
            $userData = $api->getService('userData');

            $action = null;
            // GET -> read
            // PUT -> update
            // DELETE -> delete
            // POST -> create

            switch (strtolower($request->getMethod())) {
                case 'get':
                    $action = 'list';
                    if (preg_match("/\/([0-9]+)(?=[^\/]*$)/", $request->getURI())) {
                        $action = 'read';
                    }
                    break;
                case 'post':
                    $action = 'create';
                    break;
                case 'delete':
                    $action = 'delete';
                    break;
                case 'put':
                case 'patch':
                    $action = 'update';
                    break;
                default:
                    throw new ServerErrorHttpException('No Permission define for this action');
                break;
            }
            //do you have permision
            if (!$userData->can($resource . '.' . $action)) {
                throw new PermissionException('You dont have permission to run this action ' . $action . ' at ' . $resource);
            }
        }

        return true;
    }
}
