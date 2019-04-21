<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

class PermissionException extends Exception
{
    protected $httpCode = Response::UNAUTHORIZED;
    protected $httpMessage = 'Unauthorized';
    protected $data;
}
