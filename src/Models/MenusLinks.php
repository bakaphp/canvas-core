<?php
declare(strict_types=1);

namespace Canvas\Models;

class MenusLinks extends AbstractModel
{
    public int $menus_id = 0;
    public int $parent_id = 0;
    public ?int $system_modules_id = null;
    public ?string $url = null;
    public ?string $title = null;
    public ?string $position = null;
    public ?string $icon_url = null;
    public ?string $icon_class = null;
    public ?string $route = null;
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
            ['alias' => 'childLinks', 'reusable' => true]
        );

        $this->belongsTo(
            'menus_id',
            'Canvas\Models\Menus',
            'id',
            ['alias' => 'menus', 'reusable' => true]
        );

        $this->belongsTo(
            'system_modules_id',
            'Canvas\Models\SystemModules',
            'id',
            ['alias' => 'modules', 'reusable' => true]
        );
    }

    /**
     * Check whether or not the menus link is a parent.
     *
     * @return bool
     */
    public function isParent() : bool
    {
        return $this->parent_id == 0;
    }
}
