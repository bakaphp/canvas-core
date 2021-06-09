<?php

declare(strict_types=1);

namespace Canvas\Contracts;

use Baka\Validation as CanvasValidation;
use Canvas\Auth\Auth;
use Canvas\Models\Sessions;
use Canvas\Models\Users;
use Canvas\Notifications\ResetPassword;
use Exception;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

trait AuthTrait
{
    protected $userLinkedSourcesModel;
    protected $userModel;

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
        $email = $this->request->getPost('email', 'string');
        $password = $this->request->getPost('password', 'string');
        $admin = $this->request->getPost('is_admin', 'int', 0);
        $userIp = !defined('API_TESTS') ? $this->request->getClientAddress(truetrue) : '127.0.0.1'; //help getting the client ip on scrutinizer :(
        $remember = $this->request->getPost('remember', 'int', 1);

        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('email', new PresenceOf(['message' => _('The email is required.')]));
        $validation->add('password', new PresenceOf(['message' => _('The password is required.')]));

        //validate this form for password
        $messages = $validation->validate($this->request->getPost());

        $userData = Users::login($email, $password, $remember, $admin, $userIp);

        $token = $userData->getToken();

        //start session
        $session = new Sessions();
        $session->start($userData, $token['sessionId'], $token['token'], $userIp, 1);

        return $this->response([
            'token' => $token['token'],
            'refresh_token' => $token['refresh_token'],
            'time' => date('Y-m-d H:i:s'),
            'expires' => $token['token_expiration'],
            'refresh_token_expires' => $token['refresh_token_expiration'],
            'id' => $userData->getId(),
            'timezone' => $userData->timezone
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
        $userIp = !defined('API_TESTS') ? $this->request->getClientAddress(truetrue) : '127.0.0.1';
        $data = $this->request->getPutData();
        $allDevices = isset($data['all_devices']);
        $this->userData->logOut(!$allDevices ? $userIp : null);

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
    public function signup() : Response
    {
        $user = $this->userModel;

        $request = $this->request->getPostData();

        $user->email = $this->request->getPost('email', 'email');
        $user->firstname = ltrim(trim($this->request->getPost('firstname', 'string')));
        $user->lastname = ltrim(trim($this->request->getPost('lastname', 'string')));
        $user->password = ltrim(trim($this->request->getPost('password', 'string')));
        $userIp = !defined('API_TESTS') ? $this->request->getClientAddress(true) : '127.0.0.1'; //help getting the client ip on scrutinizer :(
        $user->displayname = ltrim(trim($this->request->getPost('displayname', 'string')));
        $user->defaultCompanyName = ltrim(trim($this->request->getPost('default_company', 'string')));

        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('password', new PresenceOf(['message' => _('The password is required.')]));
        $validation->add('firstname', new PresenceOf(['message' => _('The firstname is required.')]));
        $validation->add('email', new EmailValidator(['message' => _('The email is not valid.')]));

        $validation->add(
            'password',
            new StringLength([
                'min' => 8,
                'messageMinimum' => _('Password is too short. Minimum 8 characters.'),
            ])
        );

        $validation->add('password', new Confirmation([
            'message' => _('Password and confirmation do not match.'),
            'with' => 'verify_password',
        ]));

        //validate this form for password
        $messages = $validation->validate($this->request->getPost());

        //user registration
        try {
            $this->db->begin();

            Auth::signUp($request);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();

            throw new Exception($e->getMessage());
        }

        $token = $user->getToken();

        //start session
        $session = new Sessions();
        $session->start($user, $token['sessionId'], $token['token'], $userIp, 1);

        $authSession = [
            'token' => $token['token'],
            'refresh_token' => $token['refresh_token'],
            'time' => date('Y-m-d H:i:s'),
            'expires' => date('Y-m-d H:i:s', time() + $this->config->jwt->payload->exp),
            'refresh_token_expires' => $token['refresh_token_expiration'],
            'id' => $user->getId(),
            'timezone' => $user->timezone
        ];

        $user->password = null;

        return $this->response([
            'user' => $user,
            'session' => $authSession
        ]);
    }

    /**
     * Send the user how filled out the form to the specify email
     * a link to reset his password.
     *
     * @return Response
     */
    public function recover() : Response
    {
        $request = $this->request->getPostData();

        $validation = new CanvasValidation();
        $validation->add('email', new PresenceOf(['message' => _('The email is required.')]));
        $validation->add('email', new EmailValidator(['message' => _('The email is not valid.')]));

        $validation->validate($request);

        $email = $validation->getValue('email');

        $recoverUser = Users::getByEmail($email);
        $recoverUser->generateForgotHash();

        $resetPassword = new ResetPassword($recoverUser);
        $resetPassword->setFrom($recoverUser);

        $recoverUser->notify($resetPassword);

        return $this->response(_('Check your email to recover your password'));
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
        //is the key empty or does it exists?
        if (empty($key) || !$userData = Users::findFirst(['user_activation_forgot = :key:', 'bind' => ['key' => $key]])) {
            throw new Exception(_('This Key to reset password doesn\'t exist'));
        }

        // Get the new password and the verify
        $newPassword = trim($this->request->getPost('new_password', 'string'));
        $verifyPassword = trim($this->request->getPost('verify_password', 'string'));

        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('new_password', new PresenceOf(['message' => _('The password is required.')]));
        $validation->add('new_password', new StringLength(['min' => 8, 'messageMinimum' => _('Password is too short. Minimum 8 characters.')]));

        $validation->add('new_password', new Confirmation([
            'message' => _('Passwords do not match.'),
            'with' => 'verify_password',
        ]));

        //validate this form for password
        $messages = $validation->validate($this->request->getPost());

        // Check that they are the same
        if ($newPassword == $verifyPassword) {
            // Has the password and set it
            $userData->user_activation_forgot = '';
            $userData->user_active = 1;
            $userData->password = Users::passwordHash($newPassword);

            // Update
            if ($userData->update()) {
                //log the user out of the site from all devices
                $session = new Sessions();
                $session->end($userData);

                return $this->response(_('Congratulations! You\'ve successfully changed your password.'));
            } else {
                throw new Exception(current($userData->getMessages()));
            }
        } else {
            throw new Exception(_('Password are not the same'));
        }
    }

    /**
     * User activation from the email signup.
     *
     * @method PUT
     * @url /v1/activate
     *
     * @return Response
     */
    public function activate(string $key) : Response
    {
        $userData = Users::findFirst(['user_activation_key = :key:', 'bind' => ['key' => $key]]);
        //is the key empty or does it exists?
        if (empty($key) || !$userData) {
            throw new Exception(_('This Key doesn\'t exist'));
        }

        // ok so the key exist, now is the user is not active?
        if (!$userData->isActive()) {
            //activate it
            $userData->user_active = '1';
            $userData->user_activation_key = ' ';
            $userData->update();

            $userData->password = null;

            return $this->response($userData);
        } elseif ($userData->isActive()) {
            //wtf? are you doing here and still with an activation key?
            $userData->user_activation_key = '';
            $userData->update();

            $userData->password = null;
            return $this->response($userData);
        } else {
            throw new Exception(_('This Key doesn\'t exist'));
        }
    }

    /**
     * Login user.
     *
     * @param string
     *
     * @return array
     */
    private function loginUsers(string $email, string $password) : array
    {
        $userIp = !defined('API_TESTS') ? $this->request->getClientAddress(true) : '127.0.0.1';

        $userData = Auth::login($email, $password, 1, 0, $userIp);
        $token = $userData->getToken();

        //start session
        $session = new Sessions();
        $session->start($userData, $token['sessionId'], $token['token'], $userIp, 1);

        return [
            'token' => $token['token'],
            'refresh_token' => $token['refresh_token'],
            'time' => date('Y-m-d H:i:s'),
            'expires' => $token['token_expiration'],
            'refresh_token_expires' => $token['refresh_token_expiration'],
            'id' => $userData->getId(),
            'timezone' => $userData->timezone
        ];
    }

    /**
     * User Login Social.
     *
     * @param string
     *
     * @return array
     */
    private function loginSocial(Users $user) : array
    {
        $userIp = !defined('API_TESTS') ? $this->request->getClientAddress(true) : '127.0.0.1';

        $user->lastvisit = date('Y-m-d H:i:s');
        $user->user_login_tries = 0;
        $user->user_last_login_try = 0;
        $user->update();

        $token = $user->getToken();

        //start session
        $session = new Sessions();
        $session->start($user, $token['sessionId'], $token['token'], $userIp, 1);

        return [
            'token' => $token['token'],
            'refresh_token' => $token['refresh_token'],
            'time' => date('Y-m-d H:i:s'),
            'expires' => $token['token_expiration'],
            'refresh_token_expires' => $token['refresh_token_expiration'],
            'id' => $user->getId(),
            'timezone' => $user->timezone
        ];
    }

    /**
     * Set user into Di by id.
     *
     * @param int $usersId
     *
     * @return void
     */
    private function setUserDataById(int $usersId) : void
    {
        $hostUser = Users::findFirstOrFail([
            'conditions' => 'id = ?0 and status = 1 and is_deleted = 0',
            'bind' => [$usersId]
        ]);

        /**
         * Set the host in di.
         */
        if (!$this->di->has('userData')) {
            $this->di->setShared('userData', $hostUser);
        }
    }
}
