<?php

namespace Canvas\Dto;

class Notification
{
    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var int
     */
    public $users_id;

    /**
     *
     * @var int
     */
    public $from_users_id;

    /**
     *
     * @var int
     */
    public $companies_id;

    /**
     *
     * @var int
     */
    public $apps_id;

    /**
     *
     * @var int
     */
    public $system_modules_id;

    /**
     *
     * @var int
     */
    public $notification_type_id;

    /**
     *
     * @var int
     */
    public $entity_id;
    /**
     *
     * @var string
     */
    public $content;

    /**
     *
     * @var int
     */
    public $read;

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
     * @var array
     */
    public $from;

    /**
     *
     * @var array
     */
    public $entity;

    
}
