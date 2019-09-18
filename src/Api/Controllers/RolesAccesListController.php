<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\AccessList;
use Phalcon\Http\Response;
use Phalcon\Acl\Role;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Canvas\Models\Apps;
use Canvas\Exception\NotFoundHttpException;
use Canvas\Exception\ServerErrorHttpException;
use Canvas\Models\Roles;
use Baka\Http\QueryParser;
use Canvas\Validation as CanvasValidation;

/**
 * Class RolesController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property \Canvas\Acl\Manager  $acl
 * @property \Baka\Mail\Message $mail
 * @property Apps $app
 *
 */
class RolesAccesListController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [];

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
        $this->model = new AccessList();

        //get the list of roes for the systema + my company
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', '0|' . $this->app->getId()],
        ];
    }

    /**
     * Add a new item.
     *
     * @method POST
     * @url /v1/roles-acceslist
     *
     * @return Response
     */
    public function create() : Response
    {
        $request = $this->request->getPostData();

        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('roles', new PresenceOf(['message' => _('Role information is required.')]));
        $validation->add('access', new PresenceOf(['message' => _('Access list is required.')]));

        //validate this form for password
        $validation->validate($request);

        //set the company and app
        $this->acl->setCompany($this->userData->getDefaultCompany());
        $this->acl->setApp($this->app);

        $scope = 1;
        //create the role , the scope is level 1 , that means user
        $this->acl->addRole(new Role($request['roles']['name'], $request['roles']['description']), $scope);

        /**
         * we always deny permision, by default the canvas set allow to all
         * so we only have to take away permissions.
         */
        foreach ($request['access'] as $access) {
            $this->acl->deny($request['roles']['name'], $access['resources_name'], $access['access_name']);
        }

        return $this->response($request['roles']);
    }

    /**
     * get item.
     *
     * @param mixed $id
     *
     * @method GET
     * @url /v1/roles-acceslist/{id}
     *
     * @return Response
     */
    public function getById($id) : Response
    {
        $objectInfo = $this->model->findFirst([
            'roles_id = ?0 AND is_deleted = 0 AND apps_id in (?1, ?2)',
            'bind' => [$id, $this->app->getId(), Apps::CANVAS_DEFAULT_APP_ID],
        ]);

        //get relationship
        if ($this->request->hasQuery('relationships')) {
            $relationships = $this->request->getQuery('relationships', 'string');

            $objectInfo = QueryParser::parseRelationShips($relationships, $objectInfo);
        }

        if ($objectInfo) {
            return $this->response($objectInfo);
        } else {
            throw new NotFoundHttpException('Record not found');
        }
    }

    /**
     * Update a new Entry.
     *
     * @method PUT
     * @url /v1/roles-acceslist/{id}
     *
     * @return Response
     */
    public function edit($id) : Response
    {
        $role = Roles::getById((int) $id);

        $request = $this->request->getPutData();

        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('roles', new PresenceOf(['message' => _('Role information is required.')]));
        $validation->add('access', new PresenceOf(['message' => _('Access list is required.')]));

        //validate this form for password
        $validation->validate($request);

        //set the company and app
        $this->acl->setCompany($this->userData->getDefaultCompany());
        $this->acl->setApp($this->app);

        $role->name = $request['roles']['name'];
        $role->description = $request['roles']['description'];
        $role->updateOrFail();

        //clean previous records
        $role->accesList->delete();

        /**
         * we always deny permision, by default the canvas set allow to all
         * so we only have to take away permissions.
         */
        foreach ($request['access'] as $access) {
            $this->acl->deny($request['roles']['name'], $access['resources_name'], $access['access_name']);
        }

        return $this->response($role);
    }

    /**
     * Copy a existen.
     *
     * @param int $id
     * @return Response
     */
    public function copy($id) : Response
    {
        if (!$role = Roles::getById((int) $id)) {
            throw new NotFoundHttpException('Record not found');
        }

        return $this->response($role->copy());
    }

    /**
     * delete a new Entry.
     *
     * @method DELETE
     * @url /v1/roles-acceslist/{id}
     *
     * @return Response
     */
    public function delete($id) : Response
    {
        $role = Roles::getById((int) $id);

        if ($this->softDelete == 1) {
            $role->softDelete();
        } else {
            $role->delete();
        }

        return $this->response(['Delete Successfully']);
    }
}
