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
     * Facebook Login
     * @param Sources $source
     * @param string $accessToken
     * @return Array
     */
    protected function facebook(Sources $source, string $identifier, string $email): array
    {

        /**
        * Lets find if user has a linked source by social network id
        */
        $userLinkedSource =  UserLinkedSources::findFirst([
            'conditions'=>'source_id = ?0 and source_users_id_text = ?1 and is_deleted = 0',
            'bind'=>[
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

        $existingUser = Users::findFirst([
            'conditions'=>'email = ?0 and is_deleted = 0 and status = 1',
            'bind'=>[$email]
        ]);

        /**
         * If user exists but doesnt have a linked source
         */
        if (!$userLinkedSource && $existingUser) {
            $newLinkedSource = new UserLinkedSources();
            $newLinkedSource->users_id =  $existingUser->id;
            $newLinkedSource->source_id =  $source->id;
            $newLinkedSource->source_users_id = $identifier;
            $newLinkedSource->source_users_id_text = $identifier;
            $newLinkedSource->source_username = $existingUser->displayname;
            $newLinkedSource->save();

            return $this->loginSocial($existingUser);
        }

        
        $newUser = $this->createUser($source->getId(), $identifier, $email, $password);
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
    protected function createUser(int $sourceId, string $identifier, $email, $password): Users
    {
        $random = new Random();

        //Create a new User
        $newUser = new Users();
        $newUser->firstname = 'John';
        $newUser->lastname = 'Doe';
        $newUser->displayname = 'FacebookLogin-' . $random->base58();
        $newUser->password = $password;
        $newUser->email = $email;
        $newUser->user_active = 1;
        $newUser->roles_id = 1;
        $newUser->created_at = date('Y-m-d H:m:s');
        $newUser->defaultCompanyName = 'FacebookLogin-' . $random->base58();

        try {
            $this->db->begin();

            //signup
            $newUser->signup();

            $newLinkedSource = new UserLinkedSources();
            $newLinkedSource->users_id =  $newUser->id;
            $newLinkedSource->source_id =  $sourceId;
            $newLinkedSource->source_users_id = $identifier;
            $newLinkedSource->source_users_id_text = $identifier;
            $newLinkedSource->source_username = 'FacebookLogin-' . $random->base58();
            $newLinkedSource->save();

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return $newUser;
    }
}
