<?php

namespace Canvas\Dto;

class Notification
{
    public int $id;
    public ?string $type = null;
    public ?string $title = null;
    public ?string $icon = null;
    public int $users_id;
    public ?string $users_avatar = null;
    public int $from_users_id;
    public int $companies_id;
    public int $apps_id;
    public int $system_modules_id;
    public int $notification_type_id;
    public int $entity_id;

    /**
     * @var mixed
     */
    public $content;
    public int $read = 0;
    public string $created_at;
    public ?string $updated_at = null;
    public array $from = [];

    /**
     *
     * @var mixed
     */
    public $entity;
}
