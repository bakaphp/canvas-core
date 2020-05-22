<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Http\Exception\UnprocessableEntityException;
use Canvas\Models\Sources;
use Canvas\Models\UserLinkedSources;
use Canvas\Models\Users;
use Exception;
use Phalcon\Di;
use Phalcon\Security\Random;

/**
 * Trait SocialLoginTrait.
 *
 * @package Niden\Traits
 */
trait SocialLoginTrait
{
    /**
     * Login user via Social Login Feature.
     *
     * @param Users $user
     *
     * @return array
     */
    abstract public function loginSocial(Users $user) : array;

    /**
     * Login user using stablished user credentials.
     *
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    abstract public function loginUsers(string $email, string $password) : array;

    /**
     * Social Login for many providers.
     *
     * @param Sources $source
     * @param string $accessToken
     * @param array $userInfo
     *
     * @return Array
     */
    protected function providerLogin(Sources $source, string $identifier, array $userInfo) : array
    {
        $existingUser = Users::findFirst([
            'conditions' => 'email = ?0 and is_deleted = 0 and status = 1',
            'bind' => [$userInfo['email']]
        ]);

        /**
         * Lets find if user has a linked source by social network id.
         */
        $userLinkedSource = UserLinkedSources::findFirst([
            'conditions' => 'users_id = ?0 and source_id = ?1 and source_users_id_text = ?2 and is_deleted = 0',
            'bind' => [
                $existingUser->id,
                $source->getId(),
                $identifier
            ]
        ]);

        /**
         * This confirms if the linked source exists and if it has a user attached to it. If true then logs in the user.
         */
        if ($userLinkedSource) {
            return $this->loginSocial($userLinkedSource->getUser());
        }

        /**
         * Whereas if there is no link and no user then lets create a new user and link.
         */
        $random = new Random();
        $password = $random->base58();

        /**
         * If user exists but doesnt have a linked source.
         */
        if (!$userLinkedSource && $existingUser) {
            $newLinkedSource = new UserLinkedSources();
            $newLinkedSource->users_id = $existingUser->getId();
            $newLinkedSource->source_id = $source->getId();
            $newLinkedSource->source_users_id = $identifier;
            $newLinkedSource->source_users_id_text = $identifier;
            $newLinkedSource->source_username = ucfirst($source->title) . 'Login-' . $random->base58();
            $newLinkedSource->saveOrFail();

            return $this->loginSocial($existingUser);
        }

        $newUser = $this->createUser($source, $identifier, $userInfo, $password);
        return $this->loginUsers($newUser->email, $password);
    }

    /**
     * Create a new user from social.
     *
     * @param int @sourceId
     * @param string $identifier
     * @param array $userInfo
     * @param string $password
     *
     * @return Users
     */
    protected function createUser(Sources $source, string $identifier, array $userInfo, string $password) : Users
    {
        $random = new Random();
        $appName = Di::getDefault()->getApp()->name;

        //Create a new User
        $newUser = new Users();
        $newUser->firstname = !empty($userInfo['firstname']) ? $userInfo['firstname'] : $appName;
        $newUser->lastname = !empty($userInfo['lastname']) ? $userInfo['lastname'] : 'User';
        $newUser->displayname = $appName . $random->number(99999999);
        $newUser->password = $password;
        $newUser->email = $userInfo['email'];
        $newUser->user_active = 1;
        $newUser->roles_id = 1;
        $newUser->created_at = date('Y-m-d H:m:s');
        $newUser->defaultCompanyName = $newUser->displayname . ' Company';

        try {
            $this->db->begin();

            //signup
            $newUser->signup();

            $newLinkedSource = new UserLinkedSources();
            $newLinkedSource->users_id = $newUser->id;
            $newLinkedSource->source_id = $source->getId();
            $newLinkedSource->source_users_id = $identifier;
            $newLinkedSource->source_users_id_text = $identifier;
            $newLinkedSource->source_username = ucfirst($source->title) . 'Login-' . $random->base58();
            $newLinkedSource->save();

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();

            throw new UnprocessableEntityException($e->getMessage());
        }

        return $newUser;
    }
}
