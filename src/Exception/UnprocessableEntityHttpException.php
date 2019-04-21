<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

/**
 * Using this exception when you cant save a entity
 */
class UnprocessableEntityHttpException extends HttpException
{
    protected $httpCode = Response::NOT_ACCEPTABLE;
    protected $httpMessage = 'Not Acceptable';
    protected $data;
}
