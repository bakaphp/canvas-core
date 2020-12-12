<?php

declare(strict_types=1);

namespace Canvas\Middleware;

use Canvas\Http\Response;
use Canvas\Contracts\ResponseTrait;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Mvc\User\Plugin;
use OakLabs\PhalconThrottler\ThrottlerInterface;
use Phalcon\Events\Event;
use Canvas\Exception\ServerErrorHttpException;

/**
 * Class ThrottleMiddleware
 *
 * @package Canvas\Middleware
 *
 * @property Micro    $application
 * @property Response $response
 */
class ThrottleMiddleware extends Plugin implements MiddlewareInterface
{
    use ResponseTrait;

    public function call(Micro $api)
    {
        /** @var ThrottlerInterface $throttler */
        $throttler = $this->getDI()->get('throttler');
        $rateLimit = $throttler->consume($this->request->getClientAddress());

        if ($rateLimit->isLimited()) {
            /**
             *@todo give a more informative message
             */
            throw new ServerErrorHttpException('API Calls limit reached');
        }
    }
}
