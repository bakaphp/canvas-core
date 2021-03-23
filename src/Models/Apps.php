<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Contracts\Database\HashTableTrait;
use Baka\Contracts\EventsManager\EventManagerAwareTrait;
use Baka\Database\Apps as BakaApps;
use Canvas\App\Setup;
use Canvas\Contracts\UsersAssociatedTrait;
use Phalcon\Di;
use Phalcon\Security\Random;

class Apps extends BakaApps
{
    use EventManagerAwareTrait;

    public ?string $key = null;
    public ?string $url = null;
    public ?int $is_actived = 1;
    public int $ecosystem_auth = 0;
    public int $default_apps_plan_id = 0;
    public int $payments_active = 0;
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
    const APP_DEFAULT_COUNTRY = 'default_user_country';

    /**
     * Kanvas Core App Version.
     *
     * @var string
     */
    const VERSION = 0.3;

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
            ['alias' => 'plan', 'reusable' => true]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\AppsPlans',
            'apps_id',
            [
                'alias' => 'defaultPlan',
                'reusable' => true,
                'params' => [
                    'conditions' => 'Canvas\Models\AppsPlans.is_default = 1',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\AppsPlans',
            'apps_id',
            ['alias' => 'plans', 'reusable' => true]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UserWebhooks',
            'apps_id',
            ['alias' => 'user-webhooks', 'reusable' => true]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\AppsSettings',
            'apps_id',
            ['alias' => 'settingsApp', 'reusable' => true]
        );
    }

    /**
     * Get the default Plan.
     *
     * @return AppsPlans
     */
    public function getDefaultPlan() : AppsPlans
    {
        return $this->defaultPlan;
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

        $setup = new Setup($this);
        $setup->plans()
            ->acl()
            ->systemModules()
            ->emailTemplates()
            ->defaultMenus();

        //Create a new UserAssociatedApps record
        $userAssociatedApp = new UsersAssociatedApps();
        $userAssociatedApp->users_id = Di::getDefault()->getUserData()->getId();
        $userAssociatedApp->apps_id = $this->getId();
        $userAssociatedApp->companies_id = Di::getDefault()->getUserData()->getCurrentCompany()->getId();
        $userAssociatedApp->identify_id = (string)Di::getDefault()->getUserData()->getId();
        $userAssociatedApp->user_active = 1;
        $userAssociatedApp->user_role = (string)Di::getDefault()->getUserData()->roles_id;
        $userAssociatedApp->saveOrFail();
    }

    /**
     * After Update function.
     *
     * @return void
     */
    public function afterUpdate() : void
    {
        foreach ($this->settings as $key => $value) {
            $this->set($key, $value);
        }
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

    /**
     * Get th default app currency.
     *
     * @return string
     */
    public function defaultCurrency() : string
    {
        return $this->get('currency');
    }

    /**
     * Get app by domain name.
     *
     * @param string $domain
     *
     * @return self
     */
    public static function getByDomainName(string $domain) : ?self
    {
        /**
         * @todo add cache
         */
        return self::findFirst([
            'conditions' => 'domain = :domain: AND domain_based = 1',
            'bind' => [
                'domain' => $domain
            ]
        ]);
    }
}
