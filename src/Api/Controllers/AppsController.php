<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Contracts\Http\Api\CrudBehaviorTrait;
use Canvas\Models\Apps;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Security\Random;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class AppsController extends BaseController
{
    use CrudBehaviorTrait;
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields =
    [
        'name',
        'description',
        'url',
        'default_apps_plan_id',
        'payments_active',
        'ecosystem_auth',
        'is_public'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $random = new Random();
        $this->model = new Apps();
        $this->model->key = $random->uuid();
        $this->model->is_actived = 1;
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['id', ':', implode('|', $this->userData->getAssociatedApps())],
        ];
    }

    /**
     * Process the create request and records the object.
     *
     * @return ModelInterface
     *
     * @throws Exception
     */
    protected function processCreate(RequestInterface $request) : ModelInterface
    {
        //process the input
        $request = $this->processInput($request->getPostData());

        if (array_key_exists('settings', $request)) {
            $this->model->setSettings(json_decode($request['settings'], true));
        }

        $this->model->saveOrFail($request, $this->createFields);

        return $this->model;
    }

    /**
     * get item.
     *
     * @param mixed $id
     *
     * @method GET
     * @url /v1/data/{id}
     *
     * @return \Phalcon\Http\Response
     */
    public function getById($id = null) : Response
    {
        //find the info
        $record = $this->model->findFirstOrFail([
            'id = ?0 AND is_deleted = 0 AND id in (' . implode(',', $this->userData->getAssociatedApps()) . ')',
            'bind' => [$id],
        ]);

        //get the results and append its relationships
        $result = $this->appendRelationshipsToResult($this->request, $record);

        return $this->response($this->processOutput($result));
    }

    /**
     * Delete a Record.
     *
     * @throws Exception
     *
     * @return Response
     */
    public function delete($id) : Response
    {
        return $this->response('Cant delete app at the moment');
    }
}
