<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\CompaniesCustomFields;

/**
 * Class LanguagesController
 *
 * @package Canvas\Api\Controllers
 * @property Users $userData
 *
 */
class CompaniesCustomFieldsController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['custom_fields_id', 'value'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['custom_fields_id', 'value'];

    /**
     * set objects
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new CompaniesCustomFields();
        $this->model->companies_id = $this->userData->currentCompanyId();

        $this->additionalSearchFields = [
            ['companies_id', ':', $this->userData->currentCompanyId()],
        ];
    }
}
