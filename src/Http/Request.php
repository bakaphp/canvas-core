<?php

declare(strict_types=1);

namespace Canvas\Http;

//use Phalcon\Http\Request as PhRequest;

use Baka\Contracts\Request\RequestJwtTrait;
use Baka\Http\Request\Phalcon as PhRequest;

class Request extends PhRequest
{
    use RequestJwtTrait;
}
