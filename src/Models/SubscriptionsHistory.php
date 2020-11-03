<?php

namespace Canvas\Models;

use Canvas\Traits\HistoricalRecordsTrait;

/**
 * Trait Subscription.
 *
 * @package Canvas\Models
 *
 * @property Users $user
 * @property AppsPlans $appPlan
 * @property CompanyBranches $branches
 * @property Companies $company
 * @property UserCompanyApps $app
 * @property \Phalcon\Di $di
 *
 */
class SubscriptionsHistory extends AbstractModel
{
    /**
     * Historical Records Trait
     */
    use HistoricalRecordsTrait;

    // public int $record_id;
    // public ?int $apps_plans_id = null;
    // public ?int $user_id = null;
    // public ?int $companies_id = null;
    // public ?int $apps_id = null;
    // public ?string $name = null;
    // public string $stripe_id = "";
    // public string $stripe_plan = "";
    // public int $quantity = 0;
    // public ?int $payment_frequency_id = null;
    // public ?string $trial_ends_at = null;
    // public ?int $trial_ends_days = null;
    // public int $is_freetrial = 0;
    // public int $is_active = 0;
    // public int $paid = 0;
    // public ?string $charge_date = null;
    // public ?string $ends_at = null;
    // public ?string $grace_period_ends = null;
    // public ?String $next_due_payment = null;
    // public int $is_cancelled = 0;

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        // $this->setSource('subscriptions_history');

        $this->belongsTo('user_id', 'Canvas\Models\Users', 'id', ['alias' => 'user']);

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );

        $this->belongsTo(
            'apps_plans_id',
            'Canvas\Models\AppsPlans',
            'id',
            ['alias' => 'appPlan']
        );
    }
}
