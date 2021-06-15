<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\MenusLinks;

class MenusLinksController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'menus_id',
        'parent_id',
        'system_modules_id',
        'url',
        'title',
        'position',
        'is_published',
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'menus_id',
        'parent_id',
        'system_modules_id',
        'url',
        'title',
        'position',
        'is_published',
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new MenusLinks();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];
    }
}
