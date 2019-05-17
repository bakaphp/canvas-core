<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Users;
use Canvas\Models\Companies;
use Phalcon\Http\Response;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Canvas\Exception\BadRequestHttpException;
use Baka\Http\QueryParser;
use Canvas\Exception\ModelException;
use Canvas\Exception\NotFoundHttpException;
use Canvas\Models\AccessList;

/**
 * Class UsersController
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
    protected $createFields = ['name', 'firstname', 'lastname', 'displayname', 'language', 'country_id', 'timezone', 'email', 'password', 'created_at', 'updated_at', 'default_company','default_company_branch', 'family', 'cell_phone_number', 'country_id'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['name', 'firstname', 'lastname', 'displayname', 'language', 'country_id', 'timezone', 'email', 'password', 'created_at', 'updated_at', 'default_company','default_company_branch', 'cell_phone_number', 'country_id'];

    /**
     * set objects
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
                ['default_company', ':', $this->userData->currentCompanyId()],
            ];
        }
    }

    /**
     * Get Uer
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
        //find the info
        $user = $this->model->findFirst([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$this->userData->getId()],
        ]);

        $user->password = null;

        //get relationship
        if ($this->request->hasQuery('relationships')) {
            $relationships = $this->request->getQuery('relationships', 'string');

            $user = QueryParser::parseRelationShips($relationships, $user);
        }

        //if you search for roles we give you the access for this app
        if (array_key_exists('roles', $user)) {
            $accesList = AccessList::find([
                'conditions' => 'roles_name = ?0 and apps_id = ?1 and allowed = 0',
                'bind' => [$user['roles'][0]->name, $this->config->app->id]
            ]);

            if (count($accesList) > 0) {
                foreach ($accesList as $access) {
                    $user['access_list'][strtolower($access->resources_name)][$access->access_name] = 0;
                }
            }
        }

        if ($user) {
            return $this->response($user);
        } else {
            throw new ModelException('Record not found');
        }
    }

    /**
     * Update a User Info
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

        if ($user = $this->model->findFirst($id)) {
            $request = $this->request->getPut();

            if (empty($request)) {
                $request = $this->request->getJsonRawBody(true);
            }

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

            //change my default company
            if (array_key_exists('default_company', $request)) {
                if ($company = Companies::findFirst($request['default_company'])) {
                    if ($company->userAssociatedToCompany($this->userData)) {
                        $user->default_company = $company->getId();
                        unset($request['default_company']);
                    }
                }
            }

            //update
            if ($user->update($request, $this->updateFields)) {
                $user->password = null;
                return $this->response($user);
            } else {
                //didnt work
                throw new ModelException((string)current($user->getMessages()));
            }
        } else {
            throw new NotFoundHttpException('Record not found');
        }
    }

    /**
     * Add users notifications
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
}
