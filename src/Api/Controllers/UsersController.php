<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Users;
use Canvas\Models\Companies;
use Phalcon\Http\Response;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Canvas\Exception\BadRequestHttpException;
use Canvas\Exception\ModelException;
use Canvas\Exception\NotFoundHttpException;
use Canvas\Models\AccessList;
use Canvas\Exception\ServerErrorHttpException;

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
class UsersController extends \Baka\Auth\UsersController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['name', 'firstname', 'lastname', 'displayname', 'language', 'country_id', 'timezone', 'email', 'password', 'created_at', 'updated_at', 'default_company', 'default_company_branch', 'family', 'cell_phone_number', 'country_id'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['name', 'firstname', 'lastname', 'displayname', 'language', 'country_id', 'timezone', 'email', 'password', 'created_at', 'updated_at', 'default_company', 'default_company_branch', 'cell_phone_number', 'country_id'];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Users();

        //if you are not a admin you cant see all the users
        if (!$this->userData->hasRole('Defaults.Admins')) {
            $this->additionalSearchFields = [
                ['id', ':', $this->userData->getId()],
            ];
        } else {
            //admin get all the users for this company
            $this->additionalSearchFields = [
                ['id', ':', implode('|', $this->userData->currentCompany->getAssociatedUsersByApp())],
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

        //get the results and append its relationships
        $user = $this->appendRelationshipsToResult($this->request, $user);

        //if you search for roles we give you the access for this app
        if (array_key_exists('roles', $user)) {
            $accesList = AccessList::find([
                'conditions' => 'roles_name = ?0 and apps_id = ?1 and allowed = 0',
                'bind' => [$user['roles'][0]->name, $this->app->getId()]
            ]);

            if (count($accesList) > 0) {
                foreach ($accesList as $access) {
                    $user['access_list'][strtolower($access->resources_name)][$access->access_name] = 0;
                }
            }
        }

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
        if (array_key_exists('new_password', $request) && (!empty($request['new_password']) && !empty($request['current_password']))) {
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
        if (array_key_exists('default_company', $request) && !array_key_exists('default_company_branch', $request)) {
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
     * Given the results we will proess the output
     * we will check if a DTO transformer exist and if so we will send it over to change it.
     *
     * @param object|array $results
     * @return void
     */
    protected function processOutput($results)
    {
        /**
         * remove password.
         * @todo move to DTO
         */
        if (is_object($results)) {
            $results->password = null;
            $results->bypassRoutes = null;
        }

        return $results;
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
