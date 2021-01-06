<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Exception\ForbiddenException;
use Canvas\Models\Apps;
use Canvas\Models\Roles;
use Phalcon\Http\Response;

class RolesController extends BaseController
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
        $this->model = new Roles();

        //get the list of roes for the systems + my company
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', Apps::CANVAS_DEFAULT_APP_ID . '|' . $this->acl->getApp()->getId()],
            ['companies_id', ':', '1|' . $this->userData->currentCompanyId()],
        ];
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
        $role = $this->getRecordById($id);

        if ($role->companies_id === Apps::CANVAS_DEFAULT_APP_ID) {
            throw new ForbiddenException('Cant delete a Global App Role');
        }

        if ($role->getUsers()->count() > 0) {
            throw new ForbiddenException('Cant delete a Role in use');
        }

        if ($this->softDelete) {
            $role->softDelete();
        } else {
            $role->delete();
        }

        return $this->response(['Delete Successfully']);
    }
}
