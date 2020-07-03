<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Database\Exception\ModelNotFoundException;
use function Baka\getShortClassName;
use Baka\Validation as CanvasValidation;
use Canvas\Http\Exception\NotFoundException;
use Canvas\Models\AccessList;
use Canvas\Models\Roles;
use Phalcon\Acl\Role;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\PresenceOf;

class RolesAccessListController extends BaseController
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

        //get the list of roes for the systems + my company
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', '0|' . $this->app->getId()],
        ];

        $this->customConditions = "
            AND access_list.roles_id in (SELECT id from roles WHERE companies_id = {$this->userData->currentCompanyId()} AND apps_id in (0, {$this->app->getId()}))
        ";
    }

    /**
     * Add a new item.
     *
     * @method POST
     * @url /v1/roles-accesslist
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
         * we always deny permission, by default the canvas set allow to all
         * so we only have to take away permissions.
         */
        foreach ($request['access'] as $access) {
            $this->acl->deny($request['roles']['name'], $access['resources_name'], $access['access_name']);
        }

        return $this->response($request['roles']);
    }

    /**
     * Get the element by Id
     * with the current search params user specified in the constructed.
     *
     * @param mixed $id
     *
     * @return ModelInterface|array $results
     */
    protected function getRecordById($id)
    {
        $this->additionalSearchFields[] = [
            'roles_id', ':', $id
        ];

        $processedRequest = $this->processRequest($this->request);
        $records = $this->getRecords($processedRequest);

        //get the results and append its relationships
        $results = $records['results'];

        if (empty($results) || !isset($results[0])) {
            throw new ModelNotFoundException(
                getShortClassName($this->model) . ' Record not found'
            );
        }

        return $results[0];
    }

    /**
     * Update a new Entry.
     *
     * @method PUT
     * @url /v1/roles-accesslist/{id}
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
        $role->accessList->delete();

        /**
         * we always deny permission, by default the canvas set allow to all
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
     *
     * @return Response
     */
    public function copy($id) : Response
    {
        if (!$role = Roles::getById((int) $id)) {
            throw new NotFoundException('Record not found');
        }

        return $this->response($role->copy());
    }

    /**
     * delete a new Entry.
     *
     * @method DELETE
     * @url /v1/roles-accesslist/{id}
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
