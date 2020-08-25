<?php

declare(strict_types=1);

namespace Canvas\Http\Exception;

use Canvas\Http\Response;
use Baka\Exception\HttpException;

/**
 * Critical error from the app , will send alerts
 */
class InternalServerErrorException extends HttpException
{
    protected $httpCode = Response::INTERNAL_SERVER_ERROR;
    protected $httpMessage = 'Internal Server Error';
}
