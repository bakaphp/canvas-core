<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Phalcon\Http\Response;
use DateTimeZone;

/**
 * Class TimeZonesController
 *
 * @package Canvas\Api\Controllers
 *
 */
class TimeZonesController extends BaseController
{
    /**
     * Index
     *
     * @method GET
     * @url /
     *
     * @return Response
     */
    public function index($id = null) : Response
    {
        return $this->response(DateTimeZone::listIdentifiers());
    }
}
