<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\FileSystemEntities;

class FilesystemEntitiesController extends BaseController
{
    /*
        * fields we accept to create
        *
        * @var array
        */
    protected $createFields = [
        'id',
        'filesystem_id',
        'entity_id',
        'system_modules_id',
        'companies_id',
        'field_name',
        'created_at'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'id',
        'filesystem_id',
        'entity_id',
        'system_modules_id',
        'companies_id',
        'field_name',
        'created_at'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new FileSystemEntities();
        $this->model->companies_id = $this->userData->currentCompanyId();

        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', $this->userData->currentCompanyId()]
        ];
    }
}
