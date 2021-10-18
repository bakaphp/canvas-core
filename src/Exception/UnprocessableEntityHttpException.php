<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Baka\Http\Exception\UnprocessableEntityException as BakaUnprocessableEntityException;
use Canvas\Http\Response;

/**
 * @deprecated version 0.1.5
 */
class UnprocessableEntityHttpException extends BakaUnprocessableEntityException
{
    protected int $httpCode = Response::NOT_ACCEPTABLE;
    protected string $httpMessage = 'Not Acceptable';
}
