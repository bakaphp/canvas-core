<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\UserRoles;

/**
 * Class TimeZonesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class UserRolesController extends BaseController
{
    /*
         * fields we accept to create
         *
         * @var array
         */
    protected $createFields = [];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new UserRoles();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];
    }
}
