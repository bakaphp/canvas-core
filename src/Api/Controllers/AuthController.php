<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Users;
use Canvas\Models\UserLinkedSources;
use Canvas\Exception\ServerErrorHttpException;
use Baka\Auth\Models\Users as BakaUsers;
use Canvas\Traits\AuthTrait;

/**
 * Class AuthController
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
     * Auth Trait
     */
    use AuthTrait;

    /**
     * Setup for this controller
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
     * Send email to change current email for user
     * @param int $id
     * @return bool
     */
    public function sendEmailChange(int $id): bool
    {
        //Search for user
        $user = Users::getById($id);

        if (!is_object($user)) {
            throw new NotFoundHttpException(_('User not found'));
        }

        //Send email
        if ($this->sendEmail($user, 'email-change')) {
            return true;
        }

        return false;
    }

    /**
    * Set the email config array we are going to be sending
    *
    * @param String $emailAction
    * @param Users  $user
    * @return bool
    */
    protected function sendEmail(BakaUsers $user, string $type): bool
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

            return true;
        }

        return false;
    }

    /**
     * Change user's email
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
}
