<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Traits\UsersAssociatedTrait;
use Baka\Database\Contracts\HashTableTrait;

class Apps extends \Baka\Auth\Models\Apps
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
    public $key;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var integer
     */
    public $default_apps_plan_id;

    /**
     *
     * @var integer
     */
    public $is_actived;

    /**
     * @var integer
     */
    public $payments_active;

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
     * Ecosystem default app.
     * @var string
     */
    const CANVAS_DEFAULT_APP_ID = 1;
    const CANVAS_DEFAULT_APP_NAME = 'Default';

    /**
     * Users Associated Trait.
     */
    use UsersAssociatedTrait;

    /**
     * Model Settings Trait.
     */
    use HashTableTrait;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('apps');

        $this->hasOne(
            'default_apps_plan_id',
            'Canvas\Models\AppsPlans',
            'id',
            ['alias' => 'plan']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\AppsPlans',
            'apps_id',
            ['alias' => 'plans']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UserWebhooks',
            'apps_id',
            ['alias' => 'user-webhooks']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\AppsSettings',
            'apps_id',
            ['alias' => 'settingsApp']
        );
    }

    /**
     * You can only get 2 variations or default in DB or the api app.
     *
     * @param string $name
     * @return Apps
     */
    public static function getACLApp(string $name): Apps
    {
        if (trim($name) == self::CANVAS_DEFAULT_APP_NAME) {
            $app = self::findFirst(1);
        } else {
            $app = self::findFirst(\Phalcon\DI::getDefault()->getConfig()->app->id);
        }

        return $app;
    }

    /**
     * Is active?
     *
     * @return boolean
     */
    public function isActive(): bool
    {
        return (bool) $this->is_actived;
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'apps';
    }
}
