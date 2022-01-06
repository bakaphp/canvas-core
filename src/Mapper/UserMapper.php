<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Canvas\Contracts\Mapper\RelationshipTrait;
use Canvas\Enums\Notification;
use Canvas\Models\AccessList;
use Canvas\Models\Notifications;
use Canvas\Models\Users;
use Phalcon\Di;
use Phalcon\Mvc\Model\ResultsetInterface;

class UserMapper extends CustomMapper
{
    use RelationshipTrait;

    /**
     * @param Users $source
     * @param \Canvas\Dto\User $destination
     *
     * @return \Canvas\Dto\User
     */
    public function mapToObject($source, $destination, array $context = [])
    {
        $user = $this->defaultKanvasProperties($source, $destination, $context);

        return $user;
    }

    /**
     * Set the default Kanvas UserData properties.
     *
     * @param mixed $user
     * @param object $userDto
     *
     * @return object
     */
    protected function defaultKanvasProperties($user, object $userDto, array $context) : object
    {
        if (is_array($user)) {
            $user = Users::getById($user['id']);
        }

        $userData = Di::getDefault()->get('userData');

        $userDto->id = (int)$user->id;
        $userDto->uuid = $user->uuid;
        $userDto->displayname = $user->displayname;
        $userDto->email = $user->email;
        $userDto->firstname = $user->firstname;
        $userDto->lastname = $user->lastname;
        $userDto->description = $user->description;
        $userDto->cell_phone_number = $user->cell_phone_number;
        $userDto->city_id = (int)$user->city_id;
        $userDto->country_id = (int)$user->country_id;
        $userDto->dob = $user->dob;
        $userDto->interest = $user->interest;
        $userDto->language = $user->language;
        $userDto->location = $user->location;
        $userDto->phone_number = $user->phone_number;
        $userDto->profile_header = $user->profile_header; //deprecated
        $userDto->profile_header_mobile = $user->profile_header_mobile;
        $userDto->profile_image = $user->profile_image;
        $userDto->profile_image_mobile = $user->profile_image_mobile;
        $userDto->profile_image_thumb = $user->profile_image_thumb;
        $userDto->profile_privacy = (int)$user->profile_privacy;
        $userDto->profile_remote_image = $user->profile_remote_image;
        $userDto->sex = $user->sex;
        $userDto->state_id = (int)$user->state_id;
        $userDto->status = (int)$user->status;
        $userDto->stripe_id = ''; //deprecated
        $userDto->user_active = $user->user_active;
        $userDto->system_modules_id = (int)$user->system_modules_id;
        $userDto->timezone = $user->timezone;
        $userDto->welcome = (int)$user->welcome;
        $userDto->photo = $user->photo;
        $userDto->countries = $user->countries ?: null;
        $userDto->user_activation_email = $user->user_activation_email;
        $userDto->states = $user->states ?: null;
        $userDto->cities = $user->cities ?: null;
        $userDto->registered = $user->registered;
        $userDto->new_notification = Notifications::totalUnRead($user);
        $userDto->notification_mute_all_status = (int) $user->get(Notification::USER_MUTE_ALL_STATUS);

        /**
         * Properties we need to overwrite base on the
         * current app and company the user is running.
         */
        $userDto->default_company = (int)$user->getDefaultCompany()->getId();
        $userDto->default_company_branch = (int)$user->currentBranchId();
        $userDto->roles_id = (int)$user->getPermission()->roles_id; //deprecated
        $userDto->access_list = [];

        $this->getRelationships($user, $userDto, $context);

        $userDto->roles = $this->formatRoles($user->roles);

        //hide user info if its not admin or himself
        if (!$userData->isLoggedIn() || ($userData->getId() !== $user->getId() && !$userData->isAdmin(false))) {
            $this->cleanSensitiveInfo($userDto);
        }

        if (!empty($userDto->roles)) {
            $this->accessList($userDto);
        }

        return $userDto;
    }

    /**
     * Format our role response.
     *
     * @param ResultsetInterface $roles
     *
     * @return array
     */
    protected function formatRoles(ResultsetInterface $roles) : array
    {
        $newRolesFormat = [];
        foreach ($roles as $role) {
            $newRolesFormat[] = [
                'id' => $role->getId(),
                'name' => $role->name,
                'description' => $role->description,
            ];
        }

        return $newRolesFormat;
    }

    /**
     * Attach access list to the user.
     *
     * @param object $userDto
     *
     * @return void
     */
    protected function accessList(object $userDto) : void
    {
        $app = Di::getDefault()->get('app');
        $accessList = AccessList::find([
            'conditions' => 'roles_name = ?0 AND apps_id in (?1, ?2) AND allowed = 0',
            'bind' => [
                current($userDto->roles)->name,
                $app->getId(),
                $app::CANVAS_DEFAULT_APP_ID
            ]
        ]);

        if ($accessList->count()) {
            foreach ($accessList as $access) {
                $userDto->access_list[strtolower($access->resources_name)][$access->access_name] = 0;
            }
        }
    }

    /**
     * Clean user sensitive information.
     *
     * @param object $user
     *
     * @return void
     */
    protected function cleanSensitiveInfo(object $user) : void
    {
        $user->email = '';
        $user->phone_number = '';
        $user->cell_phone_number = '';
        $user->user_activation_email = '';
        $user->dob = '';
        $user->roles = [];
    }
}
