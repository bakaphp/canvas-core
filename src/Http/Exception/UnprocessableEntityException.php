<?php

declare(strict_types=1);

namespace Canvas\Acl\Http\Exception;

use Canvas\Http\Response;
use Baka\Exception\HttpException;

class UnprocessableEntityException extends HttpException
{
    protected $httpCode = Response::UNPROCESSABLE_ENTITY;
    protected $httpMessage = 'Unprocessable Entity';
    protected $data;
}
