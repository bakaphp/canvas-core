<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Contracts\Jwt\TokenTrait as JwtTokenTrait;

/**
 * @deprecated 0.3
 */
trait TokenTrait
{
    use JwtTokenTrait;
}
