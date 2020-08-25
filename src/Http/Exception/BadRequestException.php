<?php

declare(strict_types=1);

namespace Canvas\Http\Exception;

use Baka\Exception\HttpException;
use Canvas\Http\Response;

/**
 * Using this exception when the user is trying to process something incorrectly
 * - Form validation
 * - Login validation.
 */
class BadRequestException extends HttpException
{
    protected $httpCode = Response::BAD_REQUEST;
    protected $httpMessage = 'Bad Request';
}
