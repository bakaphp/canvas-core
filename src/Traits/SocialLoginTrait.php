<?php

declare(strict_types=1);

namespace Canvas\Traits;

use function Canvas\Core\envValue;
use function time;
use Canvas\Models\Sources;
use Canvas\Models\Users;
use Canvas\Models\UserLinkedSources;
use Phalcon\Security\Random;
use Canvas\Traits\AuthTrait;

/**
 * Trait SocialLoginTrait.
 *
 * @package Niden\Traits
 */
trait SocialLoginTrait
{
    /**
     * Login user via Social Login Feature
     * @param Users $user
     * @return array
     */
    abstract public function loginSocial(Users $user): array;

    /**
     * Login user using stablished user credentials
     * @param string $email
     * @param string $password
     * @return array
     */
    abstract public function loginUsers(string $email, string $password): array;


    
    /**
     * Social Login for many providers
     * @param Sources $source
     * @param string $accessToken
     * @return Array
     */
    protected function providerLogin(Sources $source, string $identifier, string $email): array
    {

        $existingUser = Users::findFirst([
            'conditions'=>'email = ?0 and is_deleted = 0 and status = 1',
            'bind'=>[$email]
        ]);

        /**
        * Lets find if user has a linked source by social network id
        */
        $userLinkedSource =  UserLinkedSources::findFirst([
            'conditions'=>'users_id = ?0 and source_id = ?1 and source_users_id_text = ?2 and is_deleted = 0',
            'bind'=>[
                    $existingUser->getId(),
                    $source->getId(),
                    $identifier
                ]
        ]);

        /**
         * This confirms if the linked source exists and if it has a user attached to it. If true then logs in the user
         */
        if ($userLinkedSource) {
            return $this->loginSocial($userLinkedSource->getUser());
        }

        /**
         * Whereas if there is not link and not user then lets create a new user and link
         */
        $random = new Random();
        $password = $random->base58();

        /**
         * If user exists but doesnt have a linked source
         */
        if (!$userLinkedSource && $existingUser) {
            $newLinkedSource = new UserLinkedSources();
            $newLinkedSource->users_id =  $existingUser->getId();
            $newLinkedSource->source_id =  $source->getId();
            $newLinkedSource->source_users_id = $identifier;
            $newLinkedSource->source_users_id_text = $identifier;
            $newLinkedSource->source_username = ucfirst($source->title) . 'Login-' . $random->base58();
            $newLinkedSource->save();

            return $this->loginSocial($existingUser);
        }

        
        $newUser = $this->createUser($source, $identifier, $email, $password);
        return $this->loginUsers($newUser->email, $password);
    }

    /**
     * Create a new user from social
     *
     * @param int @sourceId
     * @param string $identifier
     * @param string $email
     * @param string $password
     * @return Users
     */
    protected function createUser(Sources $source, string $identifier, $email, $password): Users
    {
        $random = new Random();

        //Create a new User
        $newUser = new Users();
        $newUser->firstname = 'John';
        $newUser->lastname = 'Doe';
        $newUser->displayname =  ucfirst($source->title) . 'Login-' . $random->base58();
        $newUser->password = $password;
        $newUser->email = $email;
        $newUser->user_active = 1;
        $newUser->roles_id = 1;
        $newUser->created_at = date('Y-m-d H:m:s');
        $newUser->defaultCompanyName = ucfirst($source->title) . 'Login-' . $random->base58();

        try {
            $this->db->begin();

            //signup
            $newUser->signup();

            $newLinkedSource = new UserLinkedSources();
            $newLinkedSource->users_id =  $newUser->id;
            $newLinkedSource->source_id =  $source->getId();
            $newLinkedSource->source_users_id = $identifier;
            $newLinkedSource->source_users_id_text = $identifier;
            $newLinkedSource->source_username = ucfirst($source->title) . 'Login-' . $random->base58();
            $newLinkedSource->save();

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return $newUser;
    }
}
