<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Canvas\Http\Response;
use Canvas\Traits\ResponseTrait;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Mvc\User\Plugin;
use function Canvas\Core\isSwooleServer;

/**
 * Class NotFoundMiddleware
 *
 * @package Canvas\Middleware
 *
 * @property Micro    $application
 * @property Response $response
 */
class NotFoundMiddleware extends Plugin implements MiddlewareInterface
{
    use ResponseTrait;

    /**
     * Checks if the resource was found
     */
    public function beforeNotFound()
    {
        $apiResponse = !isSwooleServer() ? new Response() : $this->response;
        $this->halt(
            $this->application,
            Response::NOT_FOUND,
            $apiResponse->getHttpCodeDescription($apiResponse::NOT_FOUND)
        );

        return false;
    }

    /**
     * Call me
     *
     * @param Micro $api
     *
     * @return bool
     */
    public function call(Micro $api)
    {
        return true;
    }
}
