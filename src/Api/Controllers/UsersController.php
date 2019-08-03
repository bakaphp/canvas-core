<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Users;
use Phalcon\Http\Response;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Canvas\Exception\BadRequestHttpException;
use Canvas\Exception\ServerErrorHttpException;
use \Baka\Auth\UsersController as BakaUsersController;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\User as UserDto;
use Canvas\Mapper\UserMapper;

/**
 * Class UsersController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 */
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
        'displayname',
        'language',
        'country_id',
        'timezone',
        'email',
        'password',
        'created_at',
        'updated_at',
        'default_company',
        'default_company_branch',
        'family',
        'cell_phone_number',
        'country_id'
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
        'displayname',
        'language',
        'country_id',
        'timezone',
        'email',
        'password',
        'created_at',
        'updated_at',
        'default_company',
        'default_company_branch',
        'cell_phone_number',
        'country_id'
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

        /**
         * @todo filter only by user from this app / company
         */
        $user = $this->model->findFirstOrFail([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$id],
        ]);
        $userObject = $user;

        //get the results and append its relationships
        $user = $this->appendRelationshipsToResult($this->request, $user);

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

        $user = $this->model->findFirstOrFail($id);
        $request = $this->request->getPutData();

        if (empty($request)) {
            throw new BadRequestHttpException(_('No data to update this account with '));
        }

        //update password
        if (isset($request['new_password']) && (!empty($request['new_password']) && !empty($request['current_password']))) {
            //Ok let validate user password
            $validation = new Validation();
            $validation->add('new_password', new PresenceOf(['message' => 'The new_password is required.']));
            $validation->add('current_password', new PresenceOf(['message' => 'The current_password is required.']));
            $validation->add('confirm_new_password', new PresenceOf(['message' => 'The confirm_new_password is required.']));
            $messages = $validation->validate($request);

            if (count($messages)) {
                foreach ($messages as $message) {
                    throw new BadRequestHttpException((string)$message);
                }
            }

            $user->updatePassword($request['current_password'], $request['new_password'], $request['confirm_new_password']);
        } else {
            //remove on any actino that doesnt involve password
            unset($request['password']);
        }

        //change my default company , the #teamfrontend is sending us the branchid instead of the company id
        //on this value so we use is as the branch
        if (isset($request['default_company']) && !isset($request['default_company_branch'])) {
            $user->switchDefaultCompanyByBranch((int) $request['default_company']);
            unset($request['default_company'], $request['default_company_branch']);
        } else {
            $user->switchDefaultCompanyByBranch((int) $request['default_company_branch']);
            unset($request['default_company'], $request['default_company_branch']);
        }

        //update
        $user->updateOrFail($request, $this->updateFields);
        return $this->response($this->processOutput($user));
    }

    /**
     * Add users notifications.
     *
     * @param int $id
     * @method PUT
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
     * @return Response
     */
    public function delete($id): Response
    {
        if ((int) $this->userData->getId() === (int) $id) {
            throw new ServerErrorHttpException('Cant delete your own user . If you want to close your account contact support or go to app settings');
        }

        return parent::delete($id);
    }
}
