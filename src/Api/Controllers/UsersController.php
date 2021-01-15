<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Auth\UsersController as BakaUsersController;
use Baka\Validation as CanvasValidation;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\User as UserDto;
use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Mapper\UserMapper;
use Canvas\Models\Users;
use Canvas\Models\NotificationType;
use Canvas\Models\Notifications;
use Canvas\Models\UsersAssociatedApps;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\PresenceOf;

class UsersController extends BakaUsersController
{
    use ProcessOutputMapperTrait;
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'name',
        'firstname',
        'lastname',
        'description',
        'displayname',
        'language',
        'country_id',
        'timezone',
        'email',
        'password',
        'roles_id',
        'created_at',
        'updated_at',
        'default_company',
        'default_company_branch',
        'family',
        'cell_phone_number',
        'country_id',
        'location',
        'user_active'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name',
        'firstname',
        'lastname',
        'description',
        'displayname',
        'language',
        'country_id',
        'timezone',
        'email',
        'password',
        'roles_id',
        'created_at',
        'updated_at',
        'default_company',
        'default_company_branch',
        'cell_phone_number',
        'country_id',
        'location',
        'user_active'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Users();
        $this->dto = UserDto::class;
        $this->dtoMapper = new UserMapper();

        //if you are not a admin you cant see all the users
        if (!$this->userData->hasRole('Defaults.Admins')) {
            $this->additionalSearchFields = [
                ['id', ':', $this->userData->getId()],
            ];
        } else {
            //admin get all the users for this company
            $this->additionalSearchFields = [
                ['id', ':', implode('|', $this->userData->getDefaultCompany()->getAssociatedUsersByApp())],
            ];
        }
    }

    /**
     * Get Uer.
     *
     * @param mixed $id
     *
     * @method GET
     * @url /v1/users/{id}
     *
     * @return Response
     */
    public function getById($id) : Response
    {
        //none admin users can only edit themselves
        if (!$this->userData->hasRole('Default.Admins') || (int) $id === 0) {
            $id = $this->userData->getId();
        }

        $this->userData->can('SettingsMenu.company-settings');

        /**
         * @todo filter only by user from this app / company
         */
        $user = $this->model->findFirstOrFail([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$id],
        ]);

        return $this->response($this->processOutput($user));
    }

    /**
     * Update a User Info.
     *
     * @method PUT
     * @url /v1/users/{id}
     *
     * @return Response
     */
    public function edit($id) : Response
    {
        //none admin users can only edit themselves
        if (!$this->userData->hasRole('Default.Admins')) {
            $id = $this->userData->getId();
        }

        /**
         * @todo admin users should only be able to update user from their app level
         */
        $user = $this->model->findFirstOrFail($id);
        $request = $this->request->getPutData();

        if (empty($request)) {
            throw new InternalServerErrorException(_('No data to update this account with '));
        }

        //update password
        if (isset($request['new_password']) && (!empty($request['new_password']) && !empty($request['current_password']))) {
            //Ok let validate user password
            $validation = new CanvasValidation();
            $validation->add('new_password', new PresenceOf(['message' => 'The new_password is required.']));
            $validation->add('current_password', new PresenceOf(['message' => 'The current_password is required.']));
            $validation->add('confirm_new_password', new PresenceOf(['message' => 'The confirm_new_password is required.']));
            $validation->validate($request);

            $user->updatePassword($request['current_password'], $request['new_password'], $request['confirm_new_password']);
        } else {
            //remove on any actinon that doesn't involve password
            unset($request['password']);
        }

        //change my default company , the #teamfrontend is sending us the branch's instead of the company id
        //on this value so we use is as the branch
        if (isset($request['default_company']) && !isset($request['default_company_branch'])) {
            $user->switchDefaultCompanyByBranch((int) $request['default_company']);
            unset($request['default_company'], $request['default_company_branch']);
        } elseif (isset($request['default_company_branch'])) {
            $user->switchDefaultCompanyByBranch((int) $request['default_company_branch']);
            unset($request['default_company'], $request['default_company_branch']);
        }

        if (isset($request['roles_id'])) {
            $user->assignRoleById((int)$request['roles_id']);
        }

        //update

        $user->updateOrFail($request, $this->updateFields);
        return $this->response($this->processOutput($user));
    }

    /**
     * Add users notifications.
     *
     * @param int $id
     *
     * @method PUT
     *
     * @return Response
     */
    public function updateNotifications(int $id) : Response
    {
        //get the notification array
        //delete the current ones
        //iterate and save into users

        return $this->response(['OK' => $id]);
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
        if ((int) $this->userData->getId() === (int) $id) {
            throw new InternalServerErrorException(
                'Cant delete your own user . If you want to close your account contact support or go to app settings'
            );
        }

        return parent::delete($id);
    }

    /**
     * Change User's active status for in current app.
     *
     * @param int $id
     * @param int $appsId
     *
     * @throws Exception
     *
     * @return Response
     */
    public function changeAppUserActiveStatus(int $id, int $appsId) : Response
    {
        $userAssociatedToApp = UsersAssociatedApps::findFirstOrFail([
            'conditions' => 'users_id = ?0 and apps_id = ?1 and companies_id = ?2 and is_deleted = 0',
            'bind' => [$id, $this->app->getId(), $this->userData->getDefaultCompany()->getId()]
        ]);

        $userAssociatedToApp->user_active = $userAssociatedToApp->user_active ? 0 : 1;
        $userAssociatedToApp->updateOrFail();
        return $this->response($userAssociatedToApp);
    }

    /**
     * unsubscribe from notification
     *
     * @param int $id
     * @throws InternalServerErrorException
     *
     * @return Response
     */
    public function unsubscribe(int $id) : Response
    {
        $request = $this->request->getPostData();

        if (!isset($request['notification_types'])) {
            throw new Exception("Error Processing Request", 1);
        }
        
        //none admin users can only edit themselves
        if (!$this->userData->hasRole('Default.Admins')) {
            $id = $this->userData->getId();
        }

        $user = $this->model->findFirstOrFail([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$id],
        ]);

        foreach ($request['notification_types'] as $typeId) {
            $notificationType = NotificationType::findFirst($typeId);
            $systemModulesId = $notificationType ? $notificationType->system_modules_id : -1;
            Notifications::unsubscribe($user, $typeId, $systemModulesId);
        }

        return $this->response(['success']);
    }
}
