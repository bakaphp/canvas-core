<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\UsersAssociatedApps;
use Phalcon\Di;
use Phalcon\Http\Response;

class UsersAssociatedAppsController extends BaseController
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
        $this->model = new UsersAssociatedApps();

        //get the list of roes for the systema + my company
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', $this->userData->currentCompanyId()]
        ];
    }

    /**
     * Change user's active status.
     *
     * @param int $usersId
     *
     * @return Response
     */
    public function changeUserActiveStatus(int $usersId) : Response
    {
        //Verify is current user is admin
        $this->userData->isAdmin();

        $userAssociatedApp = $this->model->findFirstOrFail([
            'conditions' => 'apps_id = :apps_id: and users_id = :users_id: and companies_id = :companies_id: and is_deleted = 0',
            'bind' => [
                'apps_id' => Di::getDefault()->getApp()->getId(),
                'users_id' => $usersId,
                'companies_id' => Di::getDefault()->getUserData()->getCurrentCompany()->getId()
            ]
        ]);

        $userAssociatedApp->user_active = $userAssociatedApp->user_active ? 0 : 1;
        $userAssociatedApp->updateOrFail();

        return $this->response($userAssociatedApp);
    }
}
