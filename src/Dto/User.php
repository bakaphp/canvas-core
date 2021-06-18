<?php
declare(strict_types=1);

namespace Canvas\Dto;

class User
{
    /**
     *
     * @var int
     */
    public int $id;

    /**
     * uuid.
     *
     * @var string
     */
    public ?string $uuid = null;

    /**
     * @var array
     */
    public array $access_list;

    /**
     *
     * @var int
     */
    public ?int $active_subscription_id = 0;

    /**
     *
     * @var array
     */
    public array $bypassRoutes;

    /**
     *
     * @var string
     */
    public ?string $card_brand = null;

    /**
     *
     * @var string
     */
    public ?string $cell_phone_number = null;

    /**
     *
     * @var int
     */
    public ?int $city_id = 0;

    /**
     *
     * @var int
     */
    public ?int $country_id = 0;

    /**
     *
     * @var string
     */
    public string $created_at;

    /**
     *
     * @var int
     */
    public int $default_company;

    /**
     *
     * @var int
     */
    public ?int $default_company_branch = 0;

    /**
     *
     * @var string
     */
    public ?string $displayname = null;

    /**
     *
     * @var string
     */
    public ?string $dob = null;

    /**
     *
     * @var string
     */
    public string $email;

    /**
     *
     * @var string
     */
    public ?string $firstname = null;

    /**
     *
     * @var string
     */
    public ?string $description = null;

    /**
     *
     * @var string
     */
    public string $interest;

    /**
     *
     * @var int
     */
    public ?int $karma = null;

    /**
     *
     * @var string
     */
    public ?string $language = null;

    /**
     *
     * @var string
     */
    public ?string $lastname = null;

    /**
     *
     * @var string
     */
    public ?string $lastvisit = null;

    /**
     *
     * @var string
     */
    public ?string $location = null;

    /**
     *
     * @var string
     */
    public ?string $phone_number = null;

    /**
     *
     * @var string
     */
    public ?string $profile_header = null;

    /**
     *
     * @var string
     */
    public ?string $profile_header_mobile = null;

    /**
     *
     * @var string
     */
    public ?string $profile_image = null;

    /**
     *
     * @var string
     */
    public ?string $profile_image_mobile = null;

    /**
     *
     * @var string
     */
    public string $profile_image_thumb;

    /**
     *
     * @var string
     */
    public ?int $profile_privacy = null;

    /**
     *
     * @var string
     */
    public ?string $profile_remote_image = null;

    /**
     *
     * @var string
     */
    public ?string $registered = null;

    /**
     *
     * @var int
     */
    public int $roles_id;

    /**
     *
     * @var string
     */
    public string $session_id;

    /**
     *
     * @var string
     */
    public string $session_key;

    /**
     *
     * @var int
     */
    public ?int $session_page = 0;

    /**
     *
     * @var int
     */
    public ?int $session_time = 0;

    /**
     *
     * @var string
     */
    public ?string $sex = null;

    /**
     *
     * @var int
     */
    public ?int $state_id = null;

    /**
     *
     * @var int
     */
    public ?int $status = null;

    /**
     *
     * @var string
     */
    public ?string $stripe_id = null;

    /**
     *
     * @var string
     */
    public ?int $system_modules_id = 0;

    /**
     *
     * @var string
     */
    public ?string $timezone = null;

    /**
     *
     * @var string
     */
    public ?string $trial_ends_at = null;

    /**
     *
     * @var string
     */
    public string $updated_at;

    /**
     *
     * @var string
     */
    public $user_active;

    /**
     *
     * @var string
     */
    public ?int $user_last_login_try = null;

    /**
     *
     * @var string
     */
    public int $user_level;

    /**
     *
     * @var string
     */
    public ?int $user_login_tries = 0;

    /**
     *
     * @var int
     */
    public ?int $votes = null;

    /**
     *
     * @var string
     */
    public ?int $votes_points = null;

    /**
     *
     * @var string
     */
    public ?int $welcome = null;

    /**
     *
     * @var string
     */
    public ?string $user_activation_email = null;

    /**
     *
     * @var string
     */
    public ?Files $photo = null;
}
