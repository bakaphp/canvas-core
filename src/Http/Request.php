<?php

declare(strict_types=1);

namespace Canvas\Http;

use Phalcon\Http\Request as PhRequest;
use Canvas\Traits\RequestJwtTrait;

class Request extends PhRequest
{
    use RequestJwtTrait;
}
