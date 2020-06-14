<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Exception\ModelException;
use Phalcon\Di;

/**
 * Class AppsPlans.
 *
 * @package Canvas\Models
 *
 * @property Users $user
 * @property Config $config
 * @property Apps $app
 * @property Companies $defaultCompany
 * @property \Phalcon\Di $di
 */
class AppsPlans extends AbstractModel
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
    public $apps_id;

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
    public $stripe_id;

    /**
     *
     * @var string
     */
    public $stripe_plan;

    /**
     *
     * @var double
     */
    public $pricing;

    /**
     *
     * @var integer
     */
    public $currency_id;

    /**
     *
     * @var integer
     */
    public $free_trial_dates;

    /**
     *
     * @var integer
     */
    public $is_default;

    /**
     *
     * @var integer
     */
    public $payment_frequencies_id;

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
        $this->setSource('apps_plans');

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );

        $this->belongsTo(
            'payment_frequencies_id',
            'Canvas\Models\PaymentFrequencies',
            'id',
            ['alias' => 'paymentFrequecies']
        );

        $this->hasMany(
            'apps_id',
            'Canvas\Models\AppsPlansSettings',
            'apps_id',
            ['alias' => 'settings']
        );
    }


    /**
     * Just a preatty function that returns the same object for.
     *
     * $app->settings()->set(key, value);
     * $app->settings()->get(key);
     * $app->settings()->get(key)->delete();
     *
     * @return AppsPlans
     */
    public function settings() : AppsPlans
    {
        return $this;
    }

    /**
     * Get the default plan for this given app.
     *
     * @return AppsPlans
     */
    public static function getDefaultPlan() : AppsPlans
    {
        return AppsPlans::findFirst([
            'conditions' => 'apps_id = ?0 and is_default = 1',
            'bind' => [Di::getDefault()->getApp()->getId()]
        ]);
    }

    /**
     * Get the value of the settins by it key.
     *
     * @param string $key
     * @param string $value
     */
    public function get(string $key) : ?string
    {
        $setting = AppsPlansSettings::findFirst([
            'conditions' => 'apps_plans_id = ?0 and apps_id = ?1 and key = ?2',
            'bind' => [$this->getId(), $this->apps_id, $key]
        ]);

        if (is_object($setting)) {
            return (string) $setting->value;
        }

        return null;
    }

    /**
     * Set a setting for the given app.
     *
     * @param string $key
     * @param string $value
     */
    public function set(string $key, $value) : bool
    {
        $setting = AppsPlansSettings::findFirst([
            'conditions' => 'apps_plans_id = ?0 and apps_id = ?1 and key = ?2',
            'bind' => [$this->getId(), $this->apps_id, $key]
        ]);

        if (!is_object($setting)) {
            $setting = new AppsPlansSettings();
            $setting->apps_plans_id = $this->getId();
            $setting->apps_id = $this->getId();
            $setting->key = $key;
        }

        $setting->value = $value;

        $setting->saveOrFail();

        return true;
    }

    /**
     * After save.
     *
     * @return void
     */
    public function afterSave()
    {
        //if we udpate the is_default for this plan we need to remove all others and update the main app
        if ($this->is_default) {
            $this->app->default_apps_plan_id = $this->getId();
            $this->app->update();
        }
    }
}
