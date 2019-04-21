<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Rest\CrudExtendedController;

/**
 * Class BaseController
 *
 * @package Canvas\Api\Controllers
 *
 */
abstract class BaseController extends CrudExtendedController
{
    /**
     * activate softdelete
     * @var int
     */
    public $softDelete = 1;
}
