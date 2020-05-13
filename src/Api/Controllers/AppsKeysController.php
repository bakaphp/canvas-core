<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Http\Response;
use Canvas\Models\AppsKeys;

/**
 * Class CompaniesController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 */
class AppsKeysController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'client_id',
        'client_secret_id',
        'apps_id',
        'users_id',
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'last_used_date',
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new AppsKeys();
        $this->model->client_id = bin2hex(random_bytes(64));
        $this->model->client_secret_id = bin2hex(random_bytes(64));
        $this->model->users_id = $this->userData->getId();
        $this->model->apps_id = $this->app->getId();

        $this->additionalSearchFields = [
            ['apps_id', ':', $this->app->getId()],
        ];
    }

    /**
     * Regenerate both client id and client secret id.
     *
     * @return Response
     */
    public function regenerateKeys() : Response
    {
        $appsKeys = AppsKeys::findFirstOrFail([
            'conditions' => 'users_id = ?0 and apps_id = ?1 and is_deleted = 0',
            'bind' => [$this->userData->getId(), $this->app->getId()]
        ]);

        $appsKeys->client_id = bin2hex(random_bytes(64));
        $appsKeys->client_secret_id = bin2hex(random_bytes(64));
        $appsKeys->saveOrFail();

        return $this->response($appsKeys);
    }
}
