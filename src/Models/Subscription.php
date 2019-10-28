<?php

namespace Canvas\Models;

use Phalcon\Cashier\Subscription as PhalconSubscription;
use Canvas\Exception\ServerErrorHttpException;
use Phalcon\Di;
use Carbon\Carbon;

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
class Subscription extends PhalconSubscription
{
    /**
     *
     * @var integer
     */
    public $apps_plans_id = 0;

    /**
     *
     * @var integer
     */
    public $user_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

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
    public $stripe_id;

    /**
     *
     * @var string
     */
    public $stripe_plan;

    /**
     *
     * @var integer
     */
    public $quantity;

    /**
     *
     * @var integer
     */
    public $payment_frequency_id;

    /**
     *
     * @var string
     */
    public $trial_ends_at;

    /**
     *
     * @var integer
     */
    public $trial_ends_days;

    /**
     *
     * @var integer
     */
    public $is_freetrial;

    /**
     *
     * @var integer
     */
    public $is_active;

    /**
     *
     * @var integer
     */
    public $paid;

    /**
     *
     * @var string
     */
    public $charge_date;

    /**
     *
     * @var string
     */
    public $ends_at;

    /**
     *
     * @var date
     */
    public $grace_period_ends;

    /**
     *
     * @var datetime
     */
    public $next_due_payment;

    /**
     *
     * @var integer
     */
    public $is_cancelled;

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
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
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

    /**
     * Get the active subscription for this company app.
     *
     * @return void
     */
    public static function getActiveForThisApp() : Subscription
    {
        $subscription = self::findFirst([
            'conditions' => 'companies_id = ?0 and apps_id = ?1 and is_deleted  = 0',
            'bind' => [Di::getDefault()->getUserData()->currentCompanyId(), Di::getDefault()->getApp()->getId()]
        ]);

        if (!is_object($subscription)) {
            throw new ServerErrorHttpException(_('No active subscription for this app ' . Di::getDefault()->getApp()->getId() . ' at the company ' . Di::getDefault()->getUserData()->currentCompanyId()));
        }

        return $subscription;
    }

    /**
     * Get subscription by user's default company;.
     * @param Users $user
     * @return Subscription
     */
    public static function getByDefaultCompany(Users $user): Subscription
    {
        $subscription = self::findFirst([
            'conditions' => 'user_id = ?0 and companies_id = ?1 and apps_id = ?2 and is_deleted  = 0',
            'bind' => [$user->getId(), $user->defaultCompany->getId(), Di::getDefault()->getApp()->getId()]
        ]);

        if (!is_object($subscription)) {
            throw new ServerErrorHttpException('No active subscription for default company');
        }

        return $subscription;
    }

    /**
     * Search current company's app setting with key paid to verify payment status for current company.
     *
     * @param Users $user
     * @return bool
     */
    public static function getPaymentStatus(Users $user): bool
    {
        //if its not subscription based return true to ignore any payment status
        if (!Di::getDefault()->getApp()->subscriptioBased()) {
            return true;
        }

        if (!$user->defaultCompany->get('paid')) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Is the subscriptoin paid?
     *
     * @return boolean
     */
    public function paid(): bool
    {
        return (bool) $this->paid;
    }

    /**
     * Given a not active subscription activate it.
     *
     * @return void
     */
    public function activate(): bool
    {
        $this->is_active = 1;
        $this->paid = 1;
        $this->grace_period_ends = '';
        $this->ends_at = Carbon::now()->addDays(30)->toDateTimeString();
        $this->next_due_payment = $this->ends_at;
        $this->is_cancelled = 0;
        return $this->update();
    }

    /**
     * Determine if the subscription is within its trial period.
     *
     * @return bool
     */
    public function onTrial()
    {
        return (bool)$this->is_freetrial;
    }

    /**
     * Cancel the subscription at the end of the billing period.
     *
     * @return $this
     */
    public function cancel()
    {
        $subscription = $this->asStripeSubscription();
        $subscription->update(['cancel_at_period_end' => true]);
        $this->markAsCancelled();
        $this->save();
        return $this;
    }
}
