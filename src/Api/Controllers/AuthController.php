<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Auth\UserProvider;
use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Exception\NotFoundException;
use Baka\Validations\PasswordValidation;
use Canvas\Auth\Auth;
use Canvas\Auth\TokenResponse;
use Canvas\Contracts\AuthTrait;
use Canvas\Contracts\Jwt\TokenTrait;
use Canvas\Contracts\SocialLoginTrait;
use Canvas\Models\RegisterRoles;
use Canvas\Models\Sessions;
use Canvas\Models\Sources;
use Canvas\Models\UserLinkedSources;
use Canvas\Models\Users;
use Canvas\Notifications\PasswordUpdate;
use Canvas\Notifications\ResetPassword;
use Canvas\Notifications\Signup;
use Canvas\Notifications\UpdateEmail;
use Canvas\Validation;
use Exception;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\Email;

class AuthController extends BaseController
{
    /**
     * Auth Trait.
     */
    use AuthTrait;
    use TokenTrait;
    use SocialLoginTrait;

    /**
     * Setup for this controller.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->userLinkedSourcesModel = new UserLinkedSources();
        $this->userModel = new Users();

        if (!isset($this->config->jwt)) {
            throw new InternalServerErrorException('You need to configure your app JWT');
        }
    }

    /**
     * User Login.
     *
     * @method POST
     * @url /v1/auth
     *
     * @return Response
     */
    public function login() : Response
    {
        $request = $this->request->getPostData();

        $this->request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request['email'];
        $password = $request['password'];

        return $this->response($this->loginUsers($email, $password));
    }

    /**
     * User Signup.
     *
     * @method POST
     * @url /v1/users
     *
     * @return Response
     */
    public function signup() : Response
    {
        $user = UserProvider::get();

        $request = $this->request->getPostData();

        $this->request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $this->request->enableSanitize();

        $email = $request['email'];
        $firstname = $request['firstname'];
        $lastname = $request['lastname'];
        $displayname = $request['displayname'];
        $defaultCompany = $request['default_company'];
        $password = $request['password'];
        $verifyPassword = $request['verify_password'];
        PasswordValidation::validate($password, $verifyPassword);

        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->password = $password;
        $user->displayname = !empty($displayname) ? $displayname : $user->generateDefaultDisplayname();
        $userIp = $this->getClientIp();
        $user->defaultCompanyName = $defaultCompany;

        //user registration
        try {
            $this->db->begin();

            $user = Auth::signUp($user);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();

            throw new Exception($e->getMessage());
        }

        $sessionResponse = $this->authResponse($user);
        $user->password = '';
        $user->notify(new Signup($user));

        return $this->response([
            'user' => $user,
            'session' => $sessionResponse
        ]);
    }

    /**
     * User Login.
     *
     * @method POST
     * @url /v1/login
     *
     * @return Response
     */
    public function logout() : Response
    {
        $data = $this->request->getPutData();
        $allDevices = isset($data['all_devices']);
        Auth::logout(
            $this->userData,
            !$allDevices ? $this->getToken($this->request->getBearerTokenFromHeader()) : null
        );

        return $this->response(['Logged Out']);
    }

    /**
     * User Signup.
     *
     * @method POST
     * @url /v1/users
     *
     * @return Response
     */
    public function signupByRegisterRole() : Response
    {
        $user = $this->userModel;
        $this->request->enableSanitize();
        $request = $this->request->getPostData();

        $this->request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
            'roles_uuid' => 'required|uuid',
        ]);

        $email = $request['email'];
        $firstname = $request['firstname'];
        $lastname = $request['lastname'];
        $displayname = $request['displayname'];
        $defaultCompany = $request['default_company'];
        $password = $request['password'];
        $verifyPassword = $request['verify_password'];
        PasswordValidation::validate($password, $verifyPassword);

        $registerRole = RegisterRoles::getByUuid($request['roles_uuid']);

        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->password = $password;
        $user->displayname = !empty($displayname) ? $displayname : $user->generateDefaultDisplayname();
        $user->defaultCompanyName = $defaultCompany;
        $user->roles_id = $registerRole->roles_id;

        //user registration
        try {
            $this->db->begin();

            $user = Auth::signUp($user);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();

            throw new Exception($e->getMessage());
        }

        $sessionResponse = $this->authResponse($user);
        $user->password = '';
        $user->notify(new Signup($user));

        return $this->response([
            'user' => $user,
            'session' => $sessionResponse
        ]);
    }

    /**
     * Refresh user auth.
     *
     * @return Response
     *
     * @todo Validate access_token and refresh token, session's user email and re-login
     */
    public function refresh() : Response
    {
        $this->request->enableSanitize();
        $request = $this->request->getPostData();

        $this->request->validate([
            'access_token' => 'required',
            'refresh_token' => 'required',
        ]);

        $accessToken = $this->getToken($request['access_token']);
        $refreshToken = $this->getToken($request['refresh_token']);
        $user = null;

        if (!$accessToken->isExpired()) {
            throw new InternalServerErrorException('Issued Access Token has not expired');
        }
        if ($refreshToken->isExpired()) {
            throw new InternalServerErrorException('Refresh Token has expired');
        }

        //Check if both tokens relate to the same user's email
        if ($accessToken->claims()->get('sessionId') === $refreshToken->claims()->get('sessionId') && !is_null($accessToken->claims()->get('email'))) {
            /**
             * @todo confirm the refresh token exist and is valid from the DB
             */
            $user = Users::getByEmail($accessToken->claims()->get('email'));
        }

        if (!$user) {
            throw new NotFoundException(_('User not found'));
        }

        $token = Sessions::restart(
            $user,
            $refreshToken->claims()->get('sessionId'),
            $this->getClientIp()
        );

        return $this->response(TokenResponse::format($user, $token));
    }

    /**
     * Send email to change current email for user.
     *
     * @param int $id
     *
     * @return Response
     */
    public function sendEmailChange(int $id) : Response
    {
        //Search for user
        $user = Users::getById($id);

        $user->notify(new UpdateEmail($user));

        return $this->response($user);
    }

    /**
     * Change user's email.
     *
     * @param string $hash
     *
     * @return Response
     */
    public function changeUserEmail(string $hash) : Response
    {
        $this->request->enableSanitize();
        $request = $this->request->getPostData();

        $this->request->validate([
            'password' => 'required|min:8',
            'new_email' => 'required|email',
        ]);

        $newEmail = $request['new_email'];
        $password = $request['password'];

        //Search user by key
        $user = Users::getByUserActivationEmail($hash);

        $this->db->begin();

        $user->email = $newEmail;
        $user->updateOrFail();

        if (!$userData = $this->loginUsers($user->email, $password)) {
            $this->db->rollback();
        }

        $this->db->commit();

        return $this->response($userData);
    }

    /**
     * Login user using Access Token.
     *
     * @return Response
     */
    public function loginBySocial() : Response
    {
        $this->request->enableSanitize();
        $request = $this->request->getPostData();

        $this->request->validate([
            'social_id' => 'required',
            'provider' => 'required',
        ]);

        $source = Sources::findFirstOrFail([
            'title = ?0 and is_deleted = 0',
            'bind' => [$request['provider']]
        ]);

        if ($source->isApple()) {
            $appleUserInfo = $source->validateAppleUser($request['social_id']);
            $request['social_id'] = $appleUserInfo->sub;
            $request['email'] = $appleUserInfo->email;
        }

        $emailValidation = new Validation();
        $emailValidation->add(
            'email',
            new Email([
                'The email is required'
            ])
        );
        $emailValidation->validate($request);

        return $this->response(
            $this->providerLogin($source, $request['social_id'], $request)
        );
    }

    /**
     * Reset the user password.
     *
     * @method PUT
     * @url /v1/reset
     *
     * @return Response
     */
    public function reset(string $key) : Response
    {
        //is the key empty or does it exist?
        if (empty($key) || !$userData = Users::findFirst(['user_activation_forgot = :key:', 'bind' => ['key' => $key]])) {
            throw new Exception(_('This Key to reset password doesn\'t exist'));
        }

        $this->request->enableSanitize();
        $request = $this->request->getPostData();

        $this->request->validate([
            'new_password' => 'required',
            'verify_password' => 'required',
        ]);

        // Get the new password and the verify
        $newPassword = $request['new_password'];
        $verifyPassword = $request['verify_password'];

        //Ok let validate user password
        PasswordValidation::validate($newPassword, $verifyPassword);

        // Has the password and set it
        $userData->resetPassword($newPassword);
        $userData->user_activation_forgot = '';
        $userData->updateOrFail();

        //log the user out of the site from all devices
        $session = new Sessions();
        $session->end($userData);

        $userData->notify(new PasswordUpdate($userData));

        return $this->response(_('Password Updated'));
    }

    /**
     * Send the user how filled out the form to the specify email
     * a link to reset his password.
     *
     * @return Response
     */
    public function recover() : Response
    {
        $this->request->enableSanitize();
        $request = $this->request->getPostData();

        $this->request->validate([
            'email' => 'required|email',
        ]);

        $email = $request['email'];

        $recoverUser = Users::getByEmail($email);
        $recoverUser->generateForgotHash();

        $recoverUser->notify(new ResetPassword($recoverUser));

        return $this->response(_('Check your email to recover your password'));
    }
}
