<?php

namespace Canvas\Dto;

class Filesystem
{
    /**
     *
     * @var int
     */
    public int $id;

    /**
     *
     * @var int
     */
    public int $companies_id;

    /**
     *
     * @var int
     */
    public int $apps_id;

    /**
     *
     * @var int
     */
    public int $users_id;

    /**
     *
     * @var string
     */
    public string $name;

    /**
     *
     * @var string
     */
    public string $path;

    /**
     *
     * @var string
     */
    public string $url;

    /**
     *
     * @var string
     */
    public string $size;

    /**
     *
     * @var string
     */
    public string $file_type;

    /**
     *
     * @var array
     */
    public array $attributes;
}
