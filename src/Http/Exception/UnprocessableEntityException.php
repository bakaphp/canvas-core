<?php

declare(strict_types=1);

namespace Canvas\Http\Exception;

use Canvas\Http\Response;
use Canvas\Exception\HttpException;

class UnprocessableEntityException extends HttpException
{
    protected $httpCode = Response::UNPROCESSABLE_ENTITY;
    protected $httpMessage = 'Unprocessable Entity';
    protected $data;
}
