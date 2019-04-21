<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Resources;

/**
 * Class AclResourcesController
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Apps $app
 */
class PermissionsResourcesController extends BaseController
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
     * set objects
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Resources();

        //get the list of roes for the systema + my company
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', '0|' . $this->app->getId()],
        ];
    }
}
