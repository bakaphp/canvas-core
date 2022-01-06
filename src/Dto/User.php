<?php
declare(strict_types=1);

namespace Canvas\Dto;

class User
{
    public int $id;
    public ?string $uuid = null;
    public array $bypassRoutes;
    public ?string $cell_phone_number = null;
    public ?int $city_id = 0;
    public ?int $country_id = 0;
    public int $default_company;
    public ?int $default_company_branch = 0;
    public ?string $displayname = null;
    public ?string $dob = null;
    public string $email;
    public $user_active;
    public ?string $firstname = null;
    public ?string $description = null;
    public string $interest;
    public ?string $language = null;
    public ?string $lastname = null;
    public ?string $location = null;
    public ?string $phone_number = null;
    public ?string $profile_header = null;
    public ?string $profile_header_mobile = null;
    public ?string $profile_image = null;
    public ?string $profile_image_mobile = null;
    public string $profile_image_thumb;
    public ?int $profile_privacy = null;
    public ?string $profile_remote_image = null;
    public int $roles_id;
    public ?string $sex = null;
    public ?int $state_id = null;
    public ?int $status = null;
    public ?string $stripe_id = null;
    public ?int $system_modules_id = 2;
    public ?string $timezone = null;
    public ?int $welcome = null;
    public int $new_notification = 0;
    public ?string $user_activation_email = null;
    public int $notification_mute_all_status = 0;
    public $photo;
    public $roles = [];
    public array $access_list = [];
    public ?string $registered = null;
}
