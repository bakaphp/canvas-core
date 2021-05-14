<?php

namespace Canvas\Dto;

class User
{
    /**
     *
     * @var int
     */
    public $id;

    /**
     * uuid.
     *
     * @var string
     */
    public string $uuid;

    /**
     * @var array
     */
    public $access_list;

    /**
     *
     * @var int
     */
    public $active_subscription_id;

    /**
     *
     * @var array
     */
    //public $bypassRoutes;

    /**
     *
     * @var string
     */
    public $card_brand;

    /**
     *
     * @var string
     */
    public $cell_phone_number;

    /**
     *
     * @var int
     */
    public $city_id;

    /**
     *
     * @var int
     */
    public $country_id;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var int
     */
    public $default_company;

    /**
     *
     * @var int
     */
    public $default_company_branch;

    /**
     *
     * @var string
     */
    public $displayname;

    /**
     *
     * @var string
     */
    public $dob;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var string
     */
    public $firstname;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $interest;

    /**
     *
     * @var int
     */
    public $karma;

    /**
     *
     * @var string
     */
    public $language;

    /**
     *
     * @var string
     */
    public $lastname;

    /**
     *
     * @var string
     */
    public $lastvisit;

    /**
     *
     * @var string
     */
    public $location;

    /**
     *
     * @var string
     */
    public $phone_number;

    /**
     *
     * @var string
     */
    public $profile_header;

    /**
     *
     * @var string
     */
    public $profile_header_mobile;

    /**
     *
     * @var string
     */
    public $profile_image;

    /**
     *
     * @var string
     */
    public $profile_image_mobile;

    /**
     *
     * @var string
     */
    public $profile_image_thumb;

    /**
     *
     * @var string
     */
    public $profile_privacy;

    /**
     *
     * @var string
     */
    public $profile_remote_image;

    /**
     *
     * @var string
     */
    public $registered;

    /**
     *
     * @var string
     */
    public $roles;

    /**
     *
     * @var int
     */
    public $roles_id;

    /**
     *
     * @var string
     */
    public $session_id;

    /**
     *
     * @var string
     */
    public $session_key;

    /**
     *
     * @var int
     */
    public $session_page;

    /**
     *
     * @var int
     */
    public $session_time;

    /**
     *
     * @var string
     */
    public $sex;

    /**
     *
     * @var int
     */
    public $state_id;

    /**
     *
     * @var int
     */
    public $status;

    /**
     *
     * @var string
     */
    public $stripe_id;

    /**
     *
     * @var string
     */
    public $system_modules_id;

    /**
     *
     * @var string
     */
    public $timezone;

    /**
     *
     * @var string
     */
    public $trial_ends_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     *
     * @var string
     */
    public $user_active;

    /**
     *
     * @var string
     */
    public $user_last_login_try;

    /**
     *
     * @var string
     */
    public $user_level;

    /**
     *
     * @var string
     */
    public $user_login_tries;

    /**
     *
     * @var string
     */
    public $votes;

    /**
     *
     * @var string
     */
    public $votes_points;

    /**
     *
     * @var string
     */
    public $welcome;

    /**
     *
     * @var string
     */
    public $user_activation_email;

    /**
     *
     * @var string
     */
    public $photo;
}
