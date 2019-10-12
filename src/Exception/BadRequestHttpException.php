<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

/**
 * Using this exception when the user is trying to process something incorrectly
 * - Form validation
 * - Login validation
 */
class BadRequestHttpException extends HttpException
{
    protected $httpCode = Response::BAD_REQUEST;
    protected $httpMessage = 'Bad Request';
    protected $data;
}
