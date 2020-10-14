<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Contracts\Database\HashTableTrait;
use Baka\Contracts\EventsManager\EventManagerAwareTrait;
use Baka\Database\Apps as BakaApps;
use Canvas\Cli\Jobs\Apps as JobsApps;
use Canvas\Traits\UsersAssociatedTrait;
use Phalcon\Security\Random;

class Apps extends BakaApps
{
    use EventManagerAwareTrait;

    public ?string $key = null;
    public ?string $url = null;
    public int $default_apps_plan_id;
    public ?int $is_actived = 1;
    public int $ecosystem_auth;
    public int $payments_active;
    public int $is_public = 1;
    public array $settings = [];

    /**
     * Ecosystem default app.
     *
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
     * Before Create function.
     *
     * @return void
     */
    public function beforeCreate() : void
    {
        $random = new Random();
        parent::beforeCreate();

        $this->key = $random->uuid();
        $this->is_actived = 1;
    }

    /**
     * After Create function.
     *
     * @return void
     */
    public function afterCreate() : void
    {
        foreach ($this->settings as $key => $value) {
            $this->set($key, $value);
        }

        //send job to finish app creation
        JobsApps::dispatch($this);
    }

    /**
     * Sets Apps settings.
     *
     * @param array $settings
     *
     * @return void
     */
    public function setSettings(array $settings) : void
    {
        $this->settings = $settings;
    }

    /**
     * You can only get 2 variations or default in DB or the api app.
     *
     * @param string $name
     *
     * @return Apps
     */
    public static function getACLApp(string $name) : Apps
    {
        if (trim($name) == self::CANVAS_DEFAULT_APP_NAME) {
            $app = self::findFirst(1);
        } else {
            $appByName = self::findFirstByName($name);
            $app = $appByName ?: self::findFirstByKey(\Phalcon\DI::getDefault()->getConfig()->app->id);
        }

        return $app;
    }

    /**
     * Is active?
     *
     * @return bool
     */
    public function isActive() : bool
    {
        return (bool) $this->is_actived;
    }

    /**
     * Those this app use ecosystem login or
     * the its own local login?
     *
     * @return bool
     */
    public function ecosystemAuth() : bool
    {
        return (bool) $this->ecosystem_auth;
    }

    /**
     * Is this app subscription based?
     *
     * @return bool
     */
    public function subscriptionBased() : bool
    {
        return (bool) $this->payments_active;
    }

    /**
     * Has any settings values?
     */
    public function hasSettings() : bool
    {
        return (bool) $this->getSettingsApp()->count();
    }
}
