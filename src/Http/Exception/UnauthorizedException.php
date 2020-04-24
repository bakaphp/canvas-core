<?php

declare(strict_types=1);

namespace Canvas\Http\Exception;

use Canvas\Http\Response;
use Canvas\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    protected $httpCode = Response::UNAUTHORIZED;
    protected $httpMessage = 'Unauthorized';
    protected $data;
}
