<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\CustomFieldsValues;

class CustomFieldsValuesController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'custom_fields_id',
        'label',
        'value',
        'is_default'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'custom_fields_id',
        'label',
        'value',
        'is_default'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new CustomFieldsValues();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];
    }
}
