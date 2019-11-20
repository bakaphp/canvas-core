<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\UsersInvite;
use Canvas\Models\Users;
use Canvas\Models\Roles;
use Phalcon\Security\Random;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Canvas\Exception\NotFoundHttpException;
use Phalcon\Http\Response;
use Exception;
use Canvas\Http\Exception\UnprocessableEntityException;
use Canvas\Traits\AuthTrait;
use Canvas\Notifications\Invitation;
use Canvas\Validation as CanvasValidation;

/**
 * Class LanguagesController.
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 * @property Mail $mail
 * @property Auth $auth
 * @property Payload $payload
 * @property Exp $exp
 * @property JWT $jwt
 * @package Canvas\Api\Controllers
 *
 */
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
        'app_id',
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
        'app_id',
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
        $additionaFields = [
            ['is_deleted', ':', '0'],
        ];

        if ($this->di->has('userData')) {
            $additionaFields[] = ['companies_id', ':', $this->userData->currentCompanyId()];
        }

        $this->additionalSearchFields = $additionaFields;
    }

    /**
     * Get users invite by hash.
     * @param string $hash
     * @return Response
     */
    public function getByHash(string $hash):Response
    {
        $userInvite = $this->model::findFirst([
            'conditions' => 'invite_hash =  ?0 and is_deleted = 0',
            'bind' => [$hash]
        ]);

        if (!is_object($userInvite)) {
            throw new NotFoundHttpException('Users Invite not found');
        }

        return $this->response($userInvite);
    }

    /**
     * Sets up invitation information for a would be user.
     * @return Response
     */
    public function insertInvite(): Response
    {
        $request = $this->request->getPostData();
        $random = new Random();

        $validation = new CanvasValidation();
        $validation->add('email', new PresenceOf(['message' => _('The email is required.')]));
        $validation->add('role_id', new PresenceOf(['message' => _('The role is required.')]));

        //validate this form for password
        $validation->validate($request);

        //Check if user was already was invited to current company and return message
        UsersInvite::isValid($request['email'], (int) $request['role_id']);

        //Save data to users_invite table and generate a hash for the invite
        $userInvite = $this->model;
        $userInvite->companies_id = $this->userData->getDefaultCompany()->getId();
        $userInvite->users_id = $this->userData->getId();
        $userInvite->app_id = $this->app->getId();
        $userInvite->role_id = Roles::existsById((int)$request['role_id'])->id;
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
     * @return Response
     */
    public function processUserInvite(string $hash): Response
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
            $this->userData->getDefaultCompany()->associate($userExists, $this->userData->getDefaultCompany());
        } catch (Exception $e) {
            try {
                $newUser = $usersInvite->newUser($request);

                $this->db->begin();

                //signup
                $newUser->signup();

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

        if (!defined('API_TESTS')) {
            $usersInvite->is_deleted = 1;
            $usersInvite->update();

            return $this->response($authInfo);
        }

        return $this->response($newUser);
    }
}
