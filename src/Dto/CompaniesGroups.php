<?php

namespace Canvas\Dto;

class CompaniesGroups
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
    public $name;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var array
     */
    public $companies;

    /**
     *
     * @var datetime
     */
    public $created_at;

    /**
     *
     * @var datetime
     */
    public $updated_at;

    /**
     *
     * @var integer
     */
    public $is_deleted;
}
