<?php

declare (strict_types = 1);

namespace Canvas\Exception;

use Canvas\Http\Response;

class HttpException extends Exception
{
    protected $httpCode = Response::BAD_REQUEST;
    protected $httpMessage = 'Bad Request';
    protected $data;
}
