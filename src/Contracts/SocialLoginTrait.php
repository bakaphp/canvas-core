<?php

declare(strict_types=1);

namespace Canvas\Contracts;

use Baka\Auth\UserProvider;
use Baka\Http\Exception\UnprocessableEntityException;
use Canvas\Auth\Auth;
use Canvas\Models\Sources;
use Canvas\Models\UserLinkedSources;
use Canvas\Models\Users;
use Exception;
use Phalcon\Security\Random;

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
     * Login user using established user credentials.
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
     * @return array
     */
    protected function providerLogin(Sources $source, string $identifier, array $userInfo) : array
    {
        $random = new Random();
        //if we don't find email go by social ID
        if (isset($userInfo['email']) && filter_var($userInfo['email'], FILTER_VALIDATE_EMAIL)) {
            try {
                $existingUser = Users::getByEmail($userInfo['email']);
            } catch (Exception $e) {
            }
        } else {
            if ($linked = UserLinkedSources::getBySourceAndSocialId($source, $identifier)) {
                $existingUser = $linked->user;
            }
        }

        if ($existingUser) {
            /**
             * Lets find if user has a linked source by social network id.
             */
            $userLinkedSource = UserLinkedSources::findFirst([
                'conditions' => 'users_id = ?0 and source_id = ?1 and source_users_id_text = ?2 and is_deleted = 0',
                'bind' => [
                    $existingUser->getId(),
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
             * If user exists but doesn't have a linked source.
             */
            if (!$userLinkedSource) {
                $newLinkedSource = new UserLinkedSources();
                $newLinkedSource->users_id = $existingUser->getId();
                $newLinkedSource->source_id = $source->getId();
                $newLinkedSource->source_users_id = $identifier;
                $newLinkedSource->source_users_id_text = $identifier;
                $newLinkedSource->source_username = ucfirst($source->title) . 'Login-' . $random->base58();
                $newLinkedSource->saveOrFail();

                return $this->loginSocial($existingUser);
            }
        }

        /**
         * Here if there is no link and no user then lets create a new user and link.
         */
        $password = $random->base58();

        $newUser = $this->createUser(
            $source,
            $identifier,
            $userInfo,
            $password
        );
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
        $userObj = new Users();
        //Create a new User

        $newUser = UserProvider::get();
        $newUser->firstname = $userInfo['firstname'] ?? '';
        $newUser->lastname = $userInfo['lastname'] ?? '';
        $newUser->displayname = $userObj->generateDefaultDisplayname();
        $newUser->password = $password;
        $newUser->email = $userInfo['email'];
        $newUser->phone_number = $userInfo['phone_number'] ?? '';
        $newUser->user_active = 1;
        $newUser->roles_id = 1;
        $newUser->created_at = date('Y-m-d H:m:s');
        $newUser->defaultCompanyName = $userInfo['default_company'] ?? $newUser->displayname . ' Company';
        $newUser->dob = $userInfo['dob'] ?? '';
        $newUser->address_1 = $userInfo['address_1'] ?? '';
        $newUser->address_2 = $userInfo['address_2'] ?? '';
        $newUser->zip_code = $userInfo['zip_code'] ?? '';
        $newUser->profile_image = $userInfo['profile_image'] ?? '';

        try {
            $this->db->begin();

            //signup
            $user = Auth::signUp($newUser);

            $newLinkedSource = new UserLinkedSources();
            $newLinkedSource->users_id = $user->getId();
            $newLinkedSource->source_id = $source->getId();
            $newLinkedSource->source_users_id = $identifier;
            $newLinkedSource->source_users_id_text = $identifier;
            $newLinkedSource->source_username = ucfirst($source->title) . 'Login-' . $random->base58();
            $newLinkedSource->saveOrFail();

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();

            throw new UnprocessableEntityException($e->getMessage());
        }

        return $user;
    }
}
