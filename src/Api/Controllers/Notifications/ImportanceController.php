<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers\Notifications;

use Canvas\Api\Controllers\BaseController;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\Notifications\Importance as NotificationsImportance;
use Canvas\Mapper\Notifications\Importance as MapperNotificationsImportance;
use Canvas\Models\Notifications\Importance;

class ImportanceController extends BaseController
{
    use ProcessOutputMapperTrait;

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [

    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Importance();
        $this->dto = NotificationsImportance::class;
        $this->dtoMapper = new MapperNotificationsImportance();

        $this->additionalSearchFields = [
            ['apps_id', ':', $this->app->getId()],
            ['is_deleted', ':', 0],
        ];
    }
}
