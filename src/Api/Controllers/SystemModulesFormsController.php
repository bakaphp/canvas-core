<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\SystemModulesForms;

class SystemModulesFormsController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'system_modules_id',
        'name',
        'slug',
        'form_schema'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'system_modules_id',
        'name',
        'slug',
        'form_schema'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new SystemModulesForms();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', $this->userData->currentCompanyId()],
            ['apps_id', ':', $this->app->getId()],
        ];
    }
}
