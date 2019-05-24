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
use Phalcon\Http\Response;

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
    * Set the email config array we are going to be sending
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

    /**
     * Login user using Facebook Access Token
     * @param $accessToken
     * @return Response
     */
    public function facebookTokenLogin($accessToken): Response
    {
        $providerName =  $this->request->getPost('provider','string');

        // /**
        //  * Get the Facebook adapter
        //  */
        // $facebookAdapter = $this->socialLogin->authenticate('Facebook');

        // /**
        //  * Set user's Access Token
        //  */
        // $facebookAdapter->setAccessToken($accessToken);

        // /**
        //  * Get user's profile based on set Access Token
        //  */
        // $data = $facebookAdapter->getUserProfile();


        // /**
        //  * Lets Login or Signup the user
        //  */
        // $userProfile = current($data);

        $source = Sources::findFirst([
            'conditions'=>'title = ?0 and is_deleted = 0',
            'bind'=>[$providerName]
        ]);

        if (!is_object($source)) {
            throw new NotFoundHttpException('Source not found');
        }

        /**
        * Lets find if user has a linked source by social network id
        */
        $userLinkedSource =  UserLinkedSources::findFirst([
            'conditions'=>'source_id = ?0 and source_users_id_text = ?1 and is_deleted = 0',
            'bind'=>[$source->id,$userProfile->identifier]
        ]);

        /**
         * Verify if user exists
         */
        $user = Users::findFirst([
            'conditions' => 'email = ?0 and is_deleted = 0',
            'bind' => [$userProfile->email]
        ]);

        if (!is_object($user) && !is_object($userLinkedSource)) // User does not exist and has not been linked to a source
        {
            $random = new Random();
            $password = '123456';

            //Create a new User
            $newUser = new Users();
            $newUser->firstname = $userProfile->firstName ? $userProfile->firstName : 'John';
            $newUser->lastname = $userProfile->lastName ? $userProfile->lastName : 'Doe';
            $newUser->displayname = $request->displayName;
            $newUser->password = $password;
            $newUser->email = $userProfile->email ? $userProfile->email : 'doe@gmail.com';
            $newUser->user_active = 1;
            $newUser->roles_id = 1;
            $newUser->created_at = date('Y-m-d H:m:s');
            $newUser->defaultCompanyName = 'Default-' . $random->base58();

            try {
                $this->db->begin();

                //signup
                $newUser->signup();

                $newLinkedSource = new UserLinkedSources();
                $newLinkedSource->users_id =  $newUser->id;
                $newLinkedSource->source_id =  $source->id;
                $newLinkedSource->source_users_id = $userProfile->identifier;
                $newLinkedSource->source_users_id_text = $userProfile->identifier;
                $newLinkedSource->source_username = $userProfile->displayName;
                $newLinkedSource->save();
    
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollback();

                throw new UnprocessableEntityHttpException($e->getMessage());
            }

            return $this->response($this->loginUsers($newUser->email,$password));
        }
        else // User already has been linked to a source and just wants to login with social
        {
            //Cannot login without password. Need to make another login function that logs in user if users object exists

        }



        /**
         * Disconnect Adapter
         */
        $facebookAdapter->disconnect();


        return $this->response($user);
    }
}
