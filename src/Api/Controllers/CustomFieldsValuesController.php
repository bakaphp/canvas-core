<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\CustomFieldsValues;
use Phalcon\Http\Response;
use Canvas\Http\Exception\NotFoundException;
use Canvas\Http\Exception\UnauthorizedException;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 * @property Users $userData
 * @property Apps $app
 *
 */
class CustomFieldsValuesController extends BaseController
{
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
