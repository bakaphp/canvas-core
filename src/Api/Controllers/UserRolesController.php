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
    protected $createFields = [
        'users_id',
        'roles_id',
        'apps_id',
        'companies_id'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'users_id',
        'roles_id',
        'apps_id',
        'companies_id'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new UserRoles();
        $this->additionalSearchFields = [
            ['apps_id', ':', $this->app->getId()],
            ['is_deleted', ':', '0'],
        ];
    }
}
