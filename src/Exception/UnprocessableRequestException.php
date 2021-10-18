<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

/**
 * @deprecated version 0.1.5
 */
class UnprocessableRequestException extends HttpException
{
    protected int $httpCode = Response::NOT_ACCEPTABLE;
    protected string $httpMessage = 'Not Acceptable';
}
