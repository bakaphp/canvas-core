<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Contracts\Database\HashTableTrait;
use Phalcon\Di;

class AppsPlans extends AbstractModel
{
    use HashTableTrait;
    
    public int $apps_id;
    public string $name;
    public string $description;
    public string $stripe_id;
    public string $stripe_plan;
    public float $pricing;
    public int $currency_id;
    public int $free_trial_dates;
    public int $is_default;
    public int $payment_frequencies_id;

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
            ['alias' => 'paymentFrequencies']
        );

        //remove
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
     * Just a pretty function that returns the same object for.
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

        return $setting->saveOrFail();
    }

    /**
     * After save.
     *
     * @return void
     */
    public function afterSave()
    {
        //if we update the is_default for this plan we need to remove all others and update the main app
        if ($this->is_default) {
            $this->app->default_apps_plan_id = $this->getId();
            $this->app->update();
        }
    }
}
