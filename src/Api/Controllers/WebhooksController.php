<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Webhooks;

class WebhooksController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'system_modules_id',
        'name',
        'description',
        'action',
        'format'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'system_modules_id',
        'name',
        'description',
        'action',
        'format'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Webhooks();
        $this->model->apps_id = $this->app->getId();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()],
        ];
    }
}
