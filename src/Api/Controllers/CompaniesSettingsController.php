<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\CompaniesSettings;

class CompaniesSettingsController extends BaseController
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
        $this->model = new CompaniesSettings();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', $this->userData->currentCompanyId()]
        ];
    }
}
