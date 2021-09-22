<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Baka\Http\Exception\InternalServerErrorException as BakaInternalServerErrorException;
use Canvas\Http\Response;

/**
 * @deprecated version 0.1.5
 */
class ServerErrorHttpException extends BakaInternalServerErrorException
{
}
