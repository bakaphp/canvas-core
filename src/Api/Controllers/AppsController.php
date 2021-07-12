<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Contracts\Http\Api\CrudBehaviorTrait;
use function Baka\isJson;
use Canvas\Models\Apps;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;
use Phalcon\Mvc\ModelInterface;

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
    protected $updateFields =
    [
        'name',
        'description',
        'url',
        'default_apps_plan_id',
        'payments_active',
        'ecosystem_auth',
        'is_public'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Apps();
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
     */
    protected function processCreate(RequestInterface $request) : ModelInterface
    {
        //process the input
        $request = $this->processInput($request->getPostData());

        if (array_key_exists('settings', $request) && isJson($request['settings'])) {
            $this->model->setSettings(json_decode($request['settings'], true));
        }

        $this->model->saveOrFail($request, $this->createFields);

        return $this->model;
    }

    /**
     * Process the update request and return the object.
     *
     * @param RequestInterface $request
     * @param ModelInterface $record
     *
     * @return ModelInterface
     */
    protected function processEdit(RequestInterface $request, ModelInterface $record) : ModelInterface
    {
        //process the input
        $request = $this->processInput($request->getPutData());
        if (array_key_exists('settings', $request) && isJson($request['settings'])) {
            $record->setSettings(json_decode($request['settings'], true));
        }

        $record->updateOrFail($request, $this->updateFields);

        return $record;
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
     * @return Response
     */
    public function delete($id) : Response
    {
        return $this->response('Cant delete app at the moment');
    }
}
