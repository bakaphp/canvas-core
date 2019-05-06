<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\QueryParserCustomFields;
use Canvas\Exception\UnprocessableEntityHttpException;
use Phalcon\Http\Response;
use Baka\Http\Rest\CrudCustomFieldsController;

/**
 * Class BaseController.
 *
 * @package Canvas\Api\Controllers
 * @property Users $userData
 *
 */
abstract class BaseCustomFieldsController extends CrudCustomFieldsController
{
    /**
     * Custom Model.
     */
    protected $customModel;

    /**
     * Get by Id.
     *
     * @param mixed $id
     *
     * @method GET
     * @url /v1/general/{id}
     *
     * @return Response
     */
    public function getById($id) : Response
    {
        //find the info
        $record = $this->model->findFirst([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$id],
        ]);

        if (!is_object($record)) {
            throw new UnprocessableEntityHttpException('Record not found');
        }

        $relationships = false;

        //get relationship
        if ($this->request->hasQuery('relationships')) {
            $relationships = $this->request->getQuery('relationships', 'string');
        }

        $result = !$relationships ? $record->toFullArray() : QueryParserCustomFields::parseRelationShips($relationships, $record);

        /**
         * @todo remove this with mapper or looking for a way to overwrite relationships? redis?
         */
        if (array_key_exists('files', $result)) {
            $result['files'] = $result['files']->toArray();
            foreach ($result['files'] as $key => $file) {
                $result['files'][$key]['attributes'] = $record->getFileAllAttributes((int) $file['id']);
            }
        }

        return $this->response($result);
    }

    /**
     * Add a new item.
     *
     * @method POST
     * @url /v1/general
     *
     * @return Response
     */
    public function create() : Response
    {
        $request = $this->request->getPost();

        if (empty($request)) {
            $request = $this->request->getJsonRawBody(true);
        }

        //transaction
        $this->db->begin();

        //alwasy overwrite userid
        $request['users_id'] = $this->userData->getId();

        $this->model->setCustomFields($request);
        //try to save all the fields we allow
        if ($this->model->save($request, $this->createFields)) {
            $this->db->commit();
            return $this->getById($this->model->id);
        } else {
            $this->db->rollback();
            throw new UnprocessableEntityHttpException((string) $this->model->getMessages()[0]);
        }
    }
}
