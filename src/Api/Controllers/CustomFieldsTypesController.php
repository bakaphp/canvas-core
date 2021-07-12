<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\CustomFieldsTypes;

class CustomFieldsTypesController extends BaseController
{
    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new CustomFieldsTypes();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];
    }
}
