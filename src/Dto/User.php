<?php
declare(strict_types=1);

namespace Canvas\Dto;

class User
{
    public int $id;
    public ?string $uuid = null;
    public array $access_list;
    public ?int $active_subscription_id = 0;
    public array $bypassRoutes;
    public ?string $card_brand = null;
    public ?string $cell_phone_number = null;
    public ?int $city_id = 0;
    public ?int $country_id = 0;
    public string $created_at;
    public int $default_company;
    public ?int $default_company_branch = 0;
    public ?string $displayname = null;
    public ?string $dob = null;
    public string $email;
    public ?string $firstname = null;
    public ?string $description = null;
    public string $interest;
    public ?int $karma = null;
    public ?string $language = null;
    public ?string $lastname = null;
    public ?string $lastvisit = null;
    public ?string $location = null;
    public ?string $phone_number = null;
    public ?string $profile_header = null;
    public ?string $profile_header_mobile = null;
    public ?string $profile_image = null;
    public ?string $profile_image_mobile = null;
    public string $profile_image_thumb;
    public ?int $profile_privacy = null;
    public ?string $profile_remote_image = null;
    public ?string $registered = null;
    public int $roles_id;
    public string $session_id;
    public string $session_key;
    public ?int $session_page = 0;
    public ?int $session_time = 0;
    public ?string $sex = null;
    public ?int $state_id = null;
    public ?int $status = null;
    public ?string $stripe_id = null;
    public ?int $system_modules_id = 2;
    public ?string $timezone = null;
    public ?string $trial_ends_at = null;
    public ?string $updated_at;
    public $user_active;
    public ?int $user_last_login_try = null;
    public int $user_level;
    public ?int $user_login_tries = 0;
    public ?int $votes = null;
    public ?int $votes_points = null;
    public ?int $welcome = null;
    public ?string $user_activation_email = null;
    public int $new_notification = 0;
    public $photo;
}
