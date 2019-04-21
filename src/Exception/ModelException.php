<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

class ModelException extends Exception
{
    protected $httpCode = Response::NOT_ACCEPTABLE;
    protected $httpMessage = 'Not Acceptable';
    protected $data;
}
