<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Traits\UsersAssociatedTrait;
use Baka\Contracts\Database\HashTableTrait;
use Baka\Database\Apps as BakaApps;
class Apps extends BakaApps
{
    public string $key;
    public ?string $url;
    public int $default_apps_plan_id;
    public int $is_actived;
    public int $ecosystem_auth;
    public int $payments_active;


    /**
     * Ecosystem default app.
     * @var string
     */
    const CANVAS_DEFAULT_APP_ID = 1;
    const CANVAS_DEFAULT_APP_NAME = 'Default';
    const APP_DEFAULT_ROLE_SETTING = 'default_admin_role';

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
            $app = self::findFirstByKey(\Phalcon\DI::getDefault()->getConfig()->app->id);
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
     * Those this app use ecosystem login or
     * the its own local login?
     *
     * @return boolean
     */
    public function ecosystemAuth(): bool
    {
        return (bool) $this->ecosystem_auth;
    }

    /**
     * Is this app subscription based?
     *
     * @return boolean
     */
    public function subscriptionBased(): bool
    {
        return (bool) $this->payments_active;
    }
}
