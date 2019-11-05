<?php

declare(strict_types=1);

namespace Canvas\Http\Exception;

use Canvas\Http\Response;
use Canvas\Exception\HttpException;

class NotFoundException extends HttpException
{
    protected $httpCode = Response::NOT_FOUND;
    protected $httpMessage = 'Not Found';
    protected $data;
}
