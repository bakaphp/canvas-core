<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\EmailTemplatesVariables;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 *
 */
class EmailTemplatesVariablesController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'companies_id',
        'apps_id',
        'system_modules_id',
        'users_id',
        'name',
        'value'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'companies_id',
        'apps_id',
        'system_modules_id',
        'users_id',
        'name',
        'value'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new EmailTemplatesVariables();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', '0|' . $this->userData->currentCompanyId()],
        ];
    }
}
