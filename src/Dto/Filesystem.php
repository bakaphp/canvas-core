<?php

namespace Canvas\Dto;

class Filesystem
{
    public int $id;
    public int $companies_id;
    public int $apps_id;
    public int $users_id;
    public string $name;
    public string $path;
    public string $url;
    public string $size;
    public string $file_type;
    public array $attributes = [];
}
