<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Exception\NotFoundException;
use Baka\Http\Exception\UnprocessableEntityException;
use Baka\Validation as CanvasValidation;
use Canvas\Auth\Auth;
use Canvas\Models\Roles;
use Canvas\Models\Users;
use Canvas\Models\UsersInvite;
use Canvas\Notifications\Invitation;
use Canvas\Contracts\AuthTrait;
use Exception;
use Phalcon\Http\Response;
use Phalcon\Security\Random;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

class UsersInviteController extends BaseController
{
    use AuthTrait;

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'invite_hash',
        'companies_id',
        'role_id',
        'apps_id',
        'email'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'invite_hash',
        'companies_id',
        'role_id',
        'apps_id',
        'email'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new UsersInvite();
        $additionalFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()]
        ];

        if ($this->di->has('userData')) {
            $additionalFields[] = ['companies_id', ':', $this->userData->currentCompanyId()];
        }

        $this->additionalSearchFields = $additionalFields;
    }

    /**
     * Get users invite by hash.
     *
     * @param string $hash
     *
     * @return Response
     */
    public function getByHash(string $hash) : Response
    {
        $userInvite = $this->model::findFirst([
            'conditions' => 'invite_hash =  ?0 and is_deleted = 0',
            'bind' => [$hash]
        ]);

        if (!is_object($userInvite)) {
            throw new NotFoundException('Users Invite not found');
        }

        return $this->response($userInvite);
    }

    /**
     * Sets up invitation information for a would be user.
     *
     * @return Response
     */
    public function insertInvite() : Response
    {
        $request = $this->request->getPostData();
        $random = new Random();

        $validation = new CanvasValidation();
        $validation->add('email', new PresenceOf(['message' => _('The email is required.')]));
        $validation->add('role_id', new PresenceOf(['message' => _('The role is required.')]));

        //validate this form for password
        $validation->validate($request);

        if (!defined('API_TESTS')) {
            //Check if role is not a default one.
            if (!Roles::existsById((int)$request['role_id'])->isDefault()) {
                throw new UnprocessableEntityException(
                    "Can't create a new user with a default role."
                );
            }
        }

        //Check if user was already was invited to current company and return message
        UsersInvite::isValid($request['email'], (int) $request['role_id']);

        //Save data to users_invite table and generate a hash for the invite
        $userInvite = $this->model;
        $userInvite->companies_id = $this->userData->getDefaultCompany()->getId();
        $userInvite->users_id = $this->userData->getId();
        $userInvite->apps_id = $this->app->getId();
        $userInvite->role_id = (int) Roles::existsById((int)$request['role_id'])->id;
        $userInvite->email = $request['email'];
        $userInvite->invite_hash = $random->base58();
        $userInvite->created_at = date('Y-m-d H:m:s');

        if (!$userInvite->save()) {
            throw new UnprocessableEntityException((string) current($userInvite->getMessages()));
        }

        //create temp invite users
        $tempUser = new Users();
        $tempUser->id = 0;
        $tempUser->email = $request['email'];
        $tempUser->notify(new Invitation($userInvite));

        return $this->response($userInvite);
    }

    /**
     * Add invited user to our system.
     *
     * @return Response
     */
    public function processUserInvite(string $hash) : Response
    {
        $request = $this->request->getPostData();
        $request['password'] = ltrim(trim($request['password']));

        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('password', new PresenceOf(['message' => _('The password is required.')]));

        $validation->add(
            'password',
            new StringLength([
                'min' => 8,
                'messageMinimum' => _('Password is too short. Minimum 8 characters.'),
            ])
        );

        //validate this form for password
        $validation->validate($request);

        //Lets find users_invite by hash on our database
        $usersInvite = UsersInvite::getByHash($hash);

        //set userData as the user who is inviting the user
        $this->setUserDataById((int)$usersInvite->users_id);

        try {
            //Check if user already exists
            $userExists = Users::getByEmail($usersInvite->email);
            $newUser = $userExists;
        } catch (Exception $e) {
            try {
                $newUser = $usersInvite->newUser($request);

                $this->db->begin();

                //signup
                $newUser = Auth::signUp($newUser);

                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollback();

                throw new UnprocessableEntityException($e->getMessage());
            }
        }

        //associate the user and the app + company
        $this->app->associate($newUser, $usersInvite->company);
        $this->events->fire('user:afterInvite', $newUser, $usersInvite);

        //Lets login the new user
        $authInfo = $this->loginUsers($usersInvite->email, $request['password']);
        //move to DTO
        $newUser->password = null;

        if (!defined('API_TESTS')) {
            $usersInvite->softDelete();

            return $this->response([
                'user' => $newUser,
                'session' => $authInfo
            ]);
        }

        return $this->response($newUser);
    }
}
