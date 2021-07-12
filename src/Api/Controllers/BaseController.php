<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Contracts\Http\Api\CrudBehaviorTrait;
use Baka\Http\Api\BaseController as BakaBaseController;

abstract class BaseController extends BakaBaseController
{
    use CrudBehaviorTrait;

    /**
     * activate softdelete.
     *
     * @var int
     */
    public $softDelete = 1;
}
