<?php

declare(strict_types=1);

namespace Canvas\Http\Exception;

use Baka\Exception\HttpException;
use Canvas\Http\Response;

class UnprocessableEntityException extends HttpException
{
    protected $httpCode = Response::UNPROCESSABLE_ENTITY;
    protected $httpMessage = 'Unprocessable Entity';
}
