<?php
declare(strict_types=1);

namespace Canvas\Models;

class Menus extends AbstractModel
{
    public int $apps_id = 0;
    public string $name;
    public string $slug;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('menus');

        $this->hasMany(
            'id',
            'Canvas\Models\MenusLinks',
            'menus_id',
            ['alias' => 'links']
        );
    }
}
