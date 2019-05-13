<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Api\BaseController as BakaBaseController;

/**
 * Class BaseController.
 *
 * @package Canvas\Api\Controllers
 *
 */
abstract class BaseController extends BakaBaseController
{
    /**
     * activate softdelete.
     * @var int
     */
    public $softDelete = 1;
}
