<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\CompaniesAssociations;

/**
 * Class RolesController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 */
class CompaniesAssociationsController extends BaseController
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
        $this->model = new CompaniesAssociations();

        //get the list of roes for the systema + my company
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', $this->userData->currentCompanyId()]
        ];
    }
}
