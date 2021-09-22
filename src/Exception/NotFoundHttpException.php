<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Baka\Http\Exception\NotFoundException as BakaNotFoundException;
use Canvas\Http\Response;

/**
 * @deprecated version 0.1.5
 */
class NotFoundHttpException extends BakaNotFoundException
{
}
