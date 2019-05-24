<?php

declare(strict_types=1);

namespace Canvas\Traits;

use function Canvas\Core\envValue;
use function time;
use Canvas\Models\Sources;
use Canvas\Models\Users;
use Canvas\Models\UserLinkedSources;
use Phalcon\Security\Random;


/**
 * Trait SocialLoginTrait.
 *
 * @package Niden\Traits
 */
trait SocialLoginTrait
{
    /**
     * Facebook Login
     * @param Sources $source
     * @param string $accessToken
     * @return Array
     */
    protected function facebookLogin(Sources $source, string $accessToken): Array
    {

        /**
         * Get the Facebook adapter
         */
        $facebookAdapter = $this->di->get('socialLogin')->authenticate(ucfirst($source->title));

        /**
         * Set user's Access Token
         */
        $facebookAdapter->setAccessToken($accessToken);

        /**
         * Get user's profile based on set Access Token
         */
        $data = $facebookAdapter->getUserProfile();


        /**
         * Lets Login or Signup the user
         */
        $userProfile = current($data);

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
                $newLinkedSource->source_users_id = $userProfile->identifier ? $userProfile->identifier : 'asdakelkmaefa';
                $newLinkedSource->source_users_id_text = $userProfile->identifier ? $userProfile->identifier : 'asdakelkmaefa';
                $newLinkedSource->source_username = $userProfile->displayName ? $userProfile->displayName : 'exampleasdadas';
                $newLinkedSource->save();

                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollback();

                throw new UnprocessableEntityHttpException($e->getMessage());
            }

            // $facebookAdapter->disconnect();
            return $this->loginUsers($newUser->email,$password);
        }
        else // User already has been linked to a source and just wants to login with social
        {
            // $facebookAdapter->disconnect();
            return $this->loginSocial($user);

        }
    }
    
}
