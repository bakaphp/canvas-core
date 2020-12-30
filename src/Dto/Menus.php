<?php

namespace Canvas\Dto;

class Menus
{
    public int $id;
    public int $apps_id;
    public string $name;
    public string $slug;
    public array $sidebar = [];
    public string $created_at;
    public ?string $updated_at = null;
    public int $is_deleted = 0;
}
