<?php

declare(strict_types=1);

namespace Canvas\Http\Exception;

use Canvas\Http\Response;
use Baka\Exception\HttpException;

class ForbiddenException extends HttpException
{
    protected $httpCode = Response::FORBIDDEN;
    protected $httpMessage = 'Forbidden';
    protected $data;
}
