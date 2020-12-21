<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Canvas\Contracts\Mapper\RelationshipTrait;
use Canvas\Models\AccessList;
use Canvas\Models\Users;
use Phalcon\Di;

/**
 * Class UserMapper.
 *
 * @package Canvas\Mapper
 */
class UserMapper extends CustomMapper
{
    use RelationshipTrait;

    /**
     * @param Users $user
     * @param Canvas\Dto\User $userDto
     *
     * @return Canvas\Dto\User
     */
    public function mapToObject($user, $userDto, array $context = [])
    {
        $user = $this->defaultKanvasProperties($user, $userDto, $context);

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

        $userDto->id = $user->id;
        $userDto->displayname = $user->displayname;
        $userDto->email = $user->email;
        $userDto->firstname = $user->firstname;
        $userDto->lastname = $user->lastname;
        $userDto->description = $user->description;
        $userDto->active_subscription_id = $user->active_subscription_id;
        $userDto->card_brand = $user->card_brand;
        $userDto->cell_phone_number = $user->cell_phone_number;
        $userDto->city_id = $user->city_id;
        $userDto->country_id = $user->country_id;
        $userDto->created_at = $user->created_at;
        $userDto->dob = $user->dob;
        $userDto->interest = $user->interest;
        $userDto->karma = $user->karma;
        $userDto->language = $user->language;
        $userDto->lastvisit = $user->lastvisit;
        $userDto->location = $user->location;
        $userDto->phone_number = $user->phone_number;
        $userDto->profile_header = $user->profile_header;
        $userDto->profile_header_mobile = $user->profile_header_mobile;
        $userDto->profile_image = $user->profile_image;
        $userDto->profile_image_mobile = $user->profile_image_mobile;
        $userDto->profile_image_thumb = $user->profile_image_thumb;
        $userDto->profile_privacy = $user->profile_privacy;
        $userDto->profile_remote_image = $user->profile_remote_image;
        $userDto->registered = $user->registered;
        $userDto->session_id = $user->session_id;
        $userDto->session_key = $user->session_key;
        $userDto->session_page = $user->session_page;
        $userDto->session_time = $user->session_time;
        $userDto->sex = $user->sex;
        $userDto->state_id = $user->state_id;
        $userDto->status = $user->status;
        $userDto->stripe_id = $user->stripe_id;
        $userDto->system_modules_id = $user->system_modules_id;
        $userDto->timezone = $user->timezone;
        $userDto->trial_ends_at = $user->trial_ends_at;
        $userDto->updated_at = $user->updated_at;
        $userDto->user_active = $user->user_active;
        $userDto->user_last_login_try = $user->user_last_login_try;
        $userDto->user_level = $user->user_level;
        $userDto->user_login_tries = $user->user_login_tries;
        $userDto->votes = $user->votes;
        $userDto->votes_points = $user->votes_points;
        $userDto->user_activation_email = $user->user_activation_email;
        $userDto->welcome = $user->welcome;
        $userDto->photo = $user->photo;
        $userDto->countries = $user->countries ?: null;
        $userDto->states = $user->states ?: null;
        $userDto->cities = $user->cities ?: null;

        /**
         * Properties we need to overwrite base on the
         * current app and company the user is running.
         */
        $userDto->default_company = $user->getDefaultCompany()->getId();
        $userDto->default_company_branch = $user->currentBranchId();
        $userDto->roles_id = $user->getPermission()->roles_id;
        $userDto->access_list = [];

        $this->getRelationships($user, $userDto, $context);

        if (!empty($userDto->roles)) {
            if (isset($userDto->roles[0])) {
                $this->accessList($userDto);
            }
        }

        return $userDto;
    }

    /**
     * Attach acces list to the user.
     *
     * @param object $userDto
     *
     * @return void
     */
    protected function accessList(object $userDto) : void
    {
        $app = Di::getDefault()->getApp();
        $accessList = AccessList::find([
            'conditions' => 'roles_name = ?0 and apps_id in (?1, ?2) and allowed = 0',
            'bind' => [$userDto->roles[0]->name, $app->getId(), $app::CANVAS_DEFAULT_APP_ID]
        ]);

        if (count($accessList) > 0) {
            foreach ($accessList as $access) {
                $userDto->access_list[strtolower($access->resources_name)][$access->access_name] = 0;
            }
        }
    }
}
