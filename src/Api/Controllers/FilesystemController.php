<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\FileSystem;
use Canvas\Contracts\FileManagementTrait;
use Baka\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\Filesystem as FilesystemDto;
use Canvas\Mapper\FilesystemMapper;
use Phalcon\Http\Response;

class FilesystemController extends BaseController
{
    use FileManagementTrait;
    use ProcessOutputMapperTrait;

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
    protected $updateFields = [
        'name',
        'path',
        'url',
        'size',
        'file_type'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new FileSystem();
        $this->dto = FilesystemDto::class;
        $this->dtoMapper = new FilesystemMapper();
        $this->model->users_id = $this->userData->getId();
        $this->model->companies_id = $this->userData->currentCompanyId();

        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', $this->userData->currentCompanyId()],
            ['apps_id', ':', $this->app->getId()]
        ];
    }
}
