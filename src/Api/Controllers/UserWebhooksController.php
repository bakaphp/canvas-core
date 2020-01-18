<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Http\Exception\UnprocessableEntityException;
use Canvas\Models\UserWebhooks;
use Canvas\Validation;
use Phalcon\Http\Response;
use Canvas\Webhooks;
use Phalcon\Validation\Validator\PresenceOf;
use function Canvas\Core\isJson;

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
class UserWebhooksController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'webhooks_id',
        'url',
        'method',
        'format'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'webhooks_id',
        'url',
        'method',
        'format'
    ];

    /**
     * set objects.
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

    /**
    * Given the weebhook id, we run a test for it.
    *
    * @param integer $id
    * @return Response
    */
    public function execute(string $name): Response
    {
        $request = $this->request->getPostData();
        $validation = new Validation();
        $validateJson = false;
        $validation->add('module', new PresenceOf(['message' => 'module is required to know what webhook to run']));
        $validation->add('action', new PresenceOf(['message' => 'action is required']));

        if (isset($request['data']) && !empty($request['data'])) {
            if (!isJson($request['data'])) {
                throw new UnprocessableEntityException('Data is not a valid json format text');
            }
            $validation->add('data', new PresenceOf(['message' => 'data is required']));
            $validateJson = true;
        }

        $validation->validate($request);

        $systemModule = $request['module'];
        $data = $validateJson ? json_decode($request['data'], true) : [];
        $action = $request['action'];

        return $this->response(
            Webhooks::process(
                $systemModule,
                $data,
                $action
            )
        );
    }
}
