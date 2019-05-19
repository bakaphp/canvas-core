<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\CustomFields\CustomFields;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 * @property Users $userData
 * @property Apps $app
 *
 */
class CustomFieldsController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['name', 'label', 'custom_fields_modules_id', 'fields_type_id'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['name', 'label', 'custom_fields_modules_id', 'fields_type_id'];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new CustomFields();
        $this->model->users_id = $this->userData->getId();
        $this->model->companies_id = $this->userData->currentCompanyId();
        $this->model->apps_id = $this->app->getId();

        $this->additionalSearchFields = [
            ['apps_id', ':', $this->app->getId()],
            ['companies_id', ':', $this->userData->currentCompanyId()],
        ];
    }
}
