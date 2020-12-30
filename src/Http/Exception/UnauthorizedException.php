<?php

declare(strict_types=1);

namespace Canvas\Http\Exception;

use Baka\Exception\HttpException;
use Canvas\Http\Response;

class UnauthorizedException extends HttpException
{
    protected $httpCode = Response::UNAUTHORIZED;
    protected $httpMessage = 'Unauthorized';
}
