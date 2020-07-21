<?php

namespace Canvas\Models;

use Baka\Cashier\Subscription as BakaSubscription;
use Baka\Http\Exception\InternalServerErrorException;
use Carbon\Carbon;
use Phalcon\Db\RawValue;
use Phalcon\Di;

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
class Subscription extends BakaSubscription
{
    const DEFAULT_GRACE_PERIOD_DAYS = 5;

    public ?int $apps_plans_id = null;
    public int $user_id;
    public int $companies_id;
    public int $apps_id;
    public ?string $name = null;
    public string $stripe_id;
    public string $stripe_plan;
    public int $quantity;
    public ?int $payment_frequency_id = null;
    public ?string $trial_ends_at = null;
    public ?int $trial_ends_days = null;
    public int $is_freetrial = 0;
    public int $is_active = 0;
    public int $paid = 0;
    public ?string $charge_date = null;
    public ?string $ends_at = null;
    public ?string $grace_period_ends = null;
    public ?String $next_due_payment = null;
    public int $is_cancelled = 0;

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('subscriptions');

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
     * @return Subscription
     */
    public static function getActiveForThisApp() : Subscription
    {
        return self::getByDefaultCompany(Di::getDefault()->getUserData());
    }

    /**
     * Get subscription by user's default company;.
     *
     * @param Users $user
     *
     * @return Subscription
     */
    public static function getByDefaultCompany(Users $user) : Subscription
    {
        $subscription = self::findFirst([
            'conditions' => 'companies_id = ?0 and apps_id = ?1 and is_deleted  = 0',
            'bind' => [$user->getDefaultCompany()->getId(), Di::getDefault()->getApp()->getId()]
        ]);

        if (!$subscription) {
            throw new InternalServerErrorException('No active subscription for the company: ' . $user->getDefaultCompany()->name);
        }

        return $subscription;
    }

    /**
     * Search current company's app setting with key paid to verify payment status for current company.
     *
     * @param Users $user
     *
     * @return bool
     */
    public static function getPaymentStatus(Users $user) : bool
    {
        //if its not subscription based return true to ignore any payment status
        if (!Di::getDefault()->getApp()->subscriptionBased()) {
            return true;
        }

        if (!self::getByDefaultCompany($user)->paid()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active() : bool
    {
        if (!Di::getDefault()->getApp()->subscriptionBased()) {
            return true;
        }

        return (bool) $this->is_active;
    }

    /**
     * Is the subscriptoin paid?
     *
     * @return boolean
     */
    public function paid() : bool
    {
        if (!Di::getDefault()->getApp()->subscriptionBased()) {
            return true;
        }

        return (bool) $this->paid;
    }

    /**
     * Given a not active subscription activate it.
     *
     * @return void
     */
    public function activate() : bool
    {
        $this->is_active = 1;
        $this->paid = 1;
        //$this->grace_period_ends = new RawValue('NULL');
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
        return (bool) $this->is_freetrial;
    }

    /**
     * Get actual subscription.
     */
    public static function getActiveSubscription() : self
    {
        $userSubscription = self::findFirstOrFail([
            'conditions' => 'companies_id = ?0 and apps_id = ?1 and is_deleted  = 0',
            'bind' => [Di::getDefault()->getUserData()->currentCompanyId(), Di::getDefault()->getApp()->getId()]
        ]);

        return Di::getDefault()->getUserData()->subscription($userSubscription->name);
    }

    /**
     * Validate subscription status by grace period date and update grace period date.
     *
     * @return void
     */
    public function validateByGracePeriod() : void
    {
        if (!is_null($this->grace_period_ends)) {
            if (($this->charge_date == $this->grace_period_ends) && !$this->paid) {
                $this->is_active = 0;
                $this->grace_period_ends = Carbon::now()->addDays(Subscription::DEFAULT_GRACE_PERIOD_DAYS)->toDateTimeString();
            }
        } else {
            $this->grace_period_ends = Carbon::now()->addDays(Subscription::DEFAULT_GRACE_PERIOD_DAYS)->toDateTimeString();
        }
    }
}
