<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

/**
 * @deprecated version 0.1.5
 */
class ServerErrorHttpException extends HttpException
{
    protected $httpCode = Response::INTERNAL_SERVER_ERROR;
    protected $httpMessage = 'Internal Server Error';
    protected $data;
}
