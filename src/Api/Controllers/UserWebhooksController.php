<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\UserWebhooks;

/**
 * Class LanguagesController
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 *
 */
class UserWebhooksController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['webhooks_id', 'url', 'method', 'format'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['webhooks_id', 'url', 'method', 'format'];

    /**
     * set objects
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new UserWebhooks();
        $this->model->users_id = $this->userData->getId();
        $this->model->companies_id = $this->userData->currentCompanyId();
        $this->model->apps_id = $this->app->getId();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()],
            ['companies_id', ':', '0|' . $this->userData->currentCompanyId()],
        ];
    }
}
