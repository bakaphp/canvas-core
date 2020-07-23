<?php
declare(strict_types=1);

namespace Canvas\Models;

class MenusLinks extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $menus_id;

    /**
     *
     * @var integer
     */
    public $parent_id;

    /**
     *
     * @var integer
     */
    public $system_modules_id;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var string
     */
    public $title;

    /**
     *
     * @var integer
     */
    public $position;

    /**
     *
     * @var string
     */
    public $icon_url;

    /**
     *
     * @var string
     */
    public $icon_class;

    /**
     *
     * @var string
     */
    public $route;

    /**
     *
     * @var integer
     */
    public $is_published;

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
     * @var integer
     */
    public $is_deleted;

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
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'menus_links';
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
