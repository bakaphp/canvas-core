<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

/**
 * @deprecated version 0.1.5
 */
class NotFoundException extends HttpException
{
    protected $httpCode = Response::NOT_FOUND;
    protected $httpMessage = 'Not Found';
    protected $data;
}
