<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

/**
 * @deprecated version 0.1.5
 */
class BadRequestHttpException extends HttpException
{
    protected $httpCode = Response::BAD_REQUEST;
    protected $httpMessage = 'Bad Request';
}
