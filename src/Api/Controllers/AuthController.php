<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Users;
use Canvas\Models\Sources;
use Canvas\Models\UserLinkedSources;
use Canvas\Exception\ServerErrorHttpException;
use Canvas\Exception\ModelException;
use Baka\Auth\Models\Users as BakaUsers;
use Canvas\Traits\AuthTrait;
use Canvas\Traits\SocialLoginTrait;
use Exception;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Baka\Auth\Models\Sessions;
use Canvas\Auth\Factory;
use Canvas\Validation as CanvasValidation;

/**
 * Class AuthController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property \Baka\Mail\Message $mail
 * @property Apps $app
 */
class AuthController extends \Baka\Auth\AuthController
{
    /**
     * Auth Trait.
     */
    use AuthTrait;
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
            throw new ServerErrorHttpException('You need to configure your app JWT');
        }
    }

    /**
     * User Login.
     * @method POST
     * @url /v1/auth
     *
     * @return Response
     */
    public function login() : Response
    {
        $email = trim($this->request->getPost('email', 'string', ''));
        $password = trim($this->request->getPost('password', 'string', ''));
        $admin = 0;
        $userIp = !defined('API_TESTS') ? $this->request->getClientAddress() : '127.0.0.1'; //help getting the client ip on scrutinizer :(
        $remember = $this->request->getPost('remember', 'int', 1);

        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('email', new PresenceOf(['message' => _('The email is required.')]));
        $validation->add('password', new PresenceOf(['message' => _('The password is required.')]));

        //validate this form for password
        $validation->validate($this->request->getPost());

        /**
         * Login the user via ecosystem or app.
         */
        $auth = Factory::create($this->app->ecosystemAuth());
        $userData = $auth::login($email, $password, $remember, $admin, $userIp);
        $token = $userData->getToken();

        //start session
        $session = new Sessions();
        $session->start($userData, $token['sessionId'], $token['token'], $userIp, 1);

        return $this->response([
            'token' => $token['token'],
            'time' => date('Y-m-d H:i:s'),
            'expires' => date('Y-m-d H:i:s', time() + $this->config->jwt->payload->exp),
            'id' => $userData->getId(),
        ]);
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

        $user->email = $this->request->getPost('email', 'email');
        $user->firstname = ltrim(trim($this->request->getPost('firstname', 'string', '')));
        $user->lastname = ltrim(trim($this->request->getPost('lastname', 'string', '')));
        $user->password = ltrim(trim($this->request->getPost('password', 'string', '')));
        $userIp = !defined('API_TESTS') ? $this->request->getClientAddress() : '127.0.0.1'; //help getting the client ip on scrutinizer :(
        $user->displayname = ltrim(trim($this->request->getPost('displayname', 'string', '')));
        $user->defaultCompanyName = ltrim(trim($this->request->getPost('default_company', 'string', '')));

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
        $validation->validate($this->request->getPost());

        //user registration
        try {
            $this->db->begin();

            $user->signup();

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
            'time' => date('Y-m-d H:i:s'),
            'expires' => date('Y-m-d H:i:s', time() + $this->config->jwt->payload->exp),
            'id' => $user->getId(),
        ];

        $user->password = null;
        $this->sendEmail($user, 'signup');

        return $this->response([
            'user' => $user,
            'session' => $authSession
        ]);
    }

    /**
     * Send email to change current email for user.
     * @param int $id
     * @return Response
     */
    public function sendEmailChange(int $id): Response
    {
        //Search for user
        $user = Users::getById($id);

        if (!is_object($user)) {
            throw new NotFoundHttpException(_('User not found'));
        }

        //Send email
        $this->sendEmail($user, 'email-change');

        return $this->response($user);
    }

    /**
    * Set the email config array we are going to be sending.
    *
    * @param String $emailAction
    * @param Users  $user
    * @return void
    */
    protected function sendEmail(BakaUsers $user, string $type): void
    {
        $send = true;
        $subject = null;
        $body = null;
        switch ($type) {
            case 'recover':
                $recoveryLink = $this->config->app->frontEndUrl . '/users/reset-password/' . $user->user_activation_forgot;
                $subject = _('Password Recovery');
                $body = sprintf(_('Click %shere%s to set a new password for your account.'), '<a href="' . $recoveryLink . '" target="_blank">', '</a>');
                // send email to recover password
                break;
            case 'reset':
                $activationUrl = $this->config->app->frontEndUrl . '/user/activate/' . $user->user_activation_key;
                $subject = _('Password Updated!');
                $body = sprintf(_('Your password was update please, use this link to activate your account: %sActivate account%s'), '<a href="' . $activationUrl . '">', '</a>');
                // send email that password was update
                break;
            case 'email-change':
                $emailChangeUrl = $this->config->app->frontEndUrl . '/user/' . $user->user_activation_email . '/email';
                $subject = _('Email Change Request');
                $body = sprintf(_('Click %shere%s to set a new email for your account.'), '<a href="' . $emailChangeUrl . '">', '</a>');
                break;
            default:
                $send = false;
                break;
        }

        if ($send) {
            $this->mail
            ->to($user->email)
            ->subject($subject)
            ->content($body)
            ->sendNow();
        }
    }

    /**
     * Change user's email.
     * @param string $hash
     * @return Response
     */
    public function changeUserEmail(string $hash): Response
    {
        $newEmail = $this->request->getPost('new_email', 'string');
        $password = $this->request->getPost('password', 'string');

        //Search user by key
        $user = Users::getByUserActivationEmail($hash);

        if (!is_object($user)) {
            throw new NotFoundHttpException(_('User not found'));
        }

        $this->db->begin();

        $user->email = $newEmail;

        if (!$user->update()) {
            throw new ModelException((string)current($user->getMessages()));
        }

        if (!$userData = $this->loginUsers($user->email, $password)) {
            $this->db->rollback();
        }

        $this->db->commit();

        return $this->response($userData);
    }

    /**
     * Login user using Access Token.
     * @return Response
     */
    public function loginBySocial(): Response
    {
        $request = $this->request->getPostData();

        $source = Sources::findFirstOrFail([
            'title = ?0 and is_deleted = 0',
            'bind' => [$request['provider']]
        ]);

        return $this->response($this->providerLogin($source, $request['social_id'], $request['email']));
    }
}
