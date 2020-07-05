<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Exception\UnauthorizedException;
use Phalcon\Mvc\Micro;

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
     *
     * @todo need to check section for auth here
     *
     * @return bool
     */
    public function call(Micro $api)
    {
        $router = $api->getService('router');
        $request = $api->getService('request');

        // explode() by / , position #1 is always the controller , so its the resource ^.^
        $matchRouter = explode('/', $router->getMatchedRoute()->getCompiledPattern());

        $resource = ucfirst(isset($matchRouter[2]) ? $matchRouter[2] : $matchRouter[1]); //2 is always the controller of the router
        $userData = $api->getService('userData');

        $action = null;
        $method = strtolower($request->getMethod());

        // GET -> read
        // PUT -> update
        // DELETE -> delete
        // POST -> create
        switch ($method) {
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
                throw new InternalServerErrorException('No Permission define for this action ' . $method);
            break;
        }

        //do you have permission
        if (!$userData->can($resource . '.' . $action)) {
            throw new UnauthorizedException('You don\'t have permission to run this action ' . $action . ' at ' . $resource);
        }

        return true;
    }
}
