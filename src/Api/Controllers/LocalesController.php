<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Locations\Countries;

/**
 * @deprecated version 0.4
 */
class LocalesController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'name',
        'code'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name',
        'code'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Countries();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];
    }
}
