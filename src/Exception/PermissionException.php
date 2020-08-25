<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

/**
 * @deprecated version 0.1.5
 */
class PermissionException extends HttpException
{
    protected $httpCode = Response::UNAUTHORIZED;
    protected $httpMessage = 'Unauthorized';
}
