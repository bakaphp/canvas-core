<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Roles;

class RolesController extends BaseController
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
        $this->model = new Roles();

        //get the list of roes for the systems + my company
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', '1|' . $this->userData->currentCompanyId()],
        ];
    }
}
