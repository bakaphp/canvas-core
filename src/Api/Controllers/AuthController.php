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
use Phalcon\Http\Response;
use Canvas\Notifications\ResetPassword;
use Exception;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Baka\Auth\Models\Sessions;
use Phalcon\Di;

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

    /**
     * Send reset email to user
     * @param string $email
     * @return void
     */
    public function sendResetEmail(): void
    {
        /**
         * Lets notify the current user about its password reset and give a link to change it. Frontend must have an endpoint called /user/reset/{key}.
         */
        $this->userData->notify(new ResetPassword($this->userData));
    }

    /**
     * Reset the user password.
     * @method PUT
     * @url /v1/reset
     *
     * @return Response
     */
    public function processReset(string $key) : Response
    {
        //is the key empty or does it existe?
        if (empty($key) || !$userData = Users::findFirst(['user_activation_forgot = :key:', 'bind' => ['key' => $key]])) {
            throw new Exception(_('This Key to reset password doesn\'t exist'));
        }

        $request = $this->request->getPost();

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
            $userData->updatePassword($request['current_password'], $request['new_password'], $request['confirm_new_password']);

            //Lets create a new user_activation_forgot
            $userData->user_activation_forgot = $userData->generateActivationKey();
            //log the user out of the site from all devices
            if ($userData->update()) {
                $session = new Sessions();
                $session->end($userData);
            }

            return $this->response(_('Congratulations! You\'ve successfully changed your password.'));
        } else {
            //remove on any actino that doesnt involve password
            unset($request['password']);
            throw new Exception(_('Password are not the same'));
        }
    }
}
