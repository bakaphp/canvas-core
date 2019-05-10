<?php

namespace Canvas\Mapper\DTO;

class DTOAppsSettings
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $key;

    /**
     *
     * @var integer
     */
    public $is_public;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var integer
     */
    public $default_apps_plan_id;

    /**
     *
     * @var integer
     */
    public $is_actived;

    /**
     *
     * @var integer
     */
    public $payments_active;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     *
     * @var integer
     */
    public $is_deleted;

    /**
     *
     * @var array
     */
    public $settings;
}
