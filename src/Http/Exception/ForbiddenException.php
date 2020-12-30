<?php

declare(strict_types=1);

namespace Canvas\Http\Exception;

use Baka\Exception\HttpException;
use Canvas\Http\Response;

class ForbiddenException extends HttpException
{
    protected $httpCode = Response::FORBIDDEN;
    protected $httpMessage = 'Forbidden';
}
