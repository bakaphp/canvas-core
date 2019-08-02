<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

class NotFoundException extends HttpException
{
    protected $httpCode = Response::NOT_FOUND;
    protected $httpMessage = 'Not Found';
    protected $data;
}
