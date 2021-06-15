<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\RegisterRoles;
use Canvas\Models\Roles;

class RegisterRolesController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['roles_id'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['roles_id'];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new RegisterRoles();

        //get the list of roes for the systema + my company
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()],
            ['companies_id', ':', $this->userData->getDefaultCompany()->getId()]
        ];
    }

    /**
     * Process the input data.
     *
     * @param array $request
     *
     * @return array
     */
    protected function processInput(array $request) : array
    {
        //Lets check if the role exists
        Roles::getById((int)$request['roles_id']);
        return $request;
    }
}
