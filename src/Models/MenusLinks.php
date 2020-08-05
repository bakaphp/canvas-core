<?php
declare(strict_types=1);

namespace Canvas\Models;

class MenusLinks extends AbstractModel
{
    public int $menus_id;

    public int $parent_id = 0;

    public ?int $system_modules_id;

    public ?string $url;

    public ?string $title;

    public ?string $position;

    public ?string $icon_url;

    public ?string $icon_class;

    public ?string $route;

    public int $is_published = 0;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('menus_links');

        $this->belongsTo(
            'parent_id',
            'Canvas\Models\MenusLinks',
            'id',
            ['alias' => 'childLinks']
        );

        $this->belongsTo(
            'menus_id',
            'Canvas\Models\Menus',
            'id',
            ['alias' => 'menus']
        );

        $this->belongsTo(
            'system_modules_id',
            'Canvas\Models\SystemModules',
            'id',
            ['alias' => 'modules']
        );
    }

    /**
     * Check whether or not the menuslink is a parent.
     *
     * @return bool
     */
    public function isParent() : bool
    {
        return $this->parent_id == 0;
    }
}
