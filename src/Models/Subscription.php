<?php

namespace Canvas\Models;

use Baka\Http\Exception\InternalServerErrorException;
use Carbon\Carbon;
use DateTimeInterface;
use LogicException;
use Phalcon\Db\RawValue;
use Phalcon\Di;
use Stripe\Subscription as StripeSubscription;

class Subscription extends AbstractModel
{
    const DEFAULT_GRACE_PERIOD_DAYS = 5;

    public ?int $apps_plans_id = null;
    public int $users_id;
    public int $companies_groups_id;
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
     * Indicates if the plan change should be prorated.
     */
    protected bool $prorate = true;

    /**
     * The date on which the billing cycle should be anchored.
     */
    protected ?string $billingCycleAnchor = null;

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
            'companies_group_id',
            'Canvas\Models\CompaniesGroups',
            'id',
            ['alias' => 'companyGroup']
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
     * Get subscription by user's default company;.
     *
     * @param Users $user
     *
     * @return Subscription
     */
    public static function getByDefaultCompany(Users $user) : Subscription
    {
        $subscription = self::findFirst([
            'conditions' => 'companies_groups_id = ?0 and apps_id = ?1 and is_deleted  = 0',
            'bind' => [
                $user->getDefaultCompany()->getDefaultCompanyGroup->getId(),
                Di::getDefault()->get('app')->getId()
            ]
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
        if (!Di::getDefault()->get('app')->subscriptionBased()) {
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
        if (!Di::getDefault()->get('app')->subscriptionBased()) {
            return true;
        }

        return (bool) $this->is_active;
    }

    /**
     * Is the subscription paid?
     *
     * @return bool
     */
    public function paid() : bool
    {
        if (!Di::getDefault()->get('app')->subscriptionBased()) {
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
    public function onTrial() : bool
    {
        return (bool) $this->is_freetrial;
    }

    /**
     * Get actual subscription.
     */
    public static function getActiveSubscription() : self
    {
        $companyGroup = Di::getDefault()->get('userData')->getDefaultCompanyGroup();

        return $companyGroup->subscription();
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

    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is no longer active.
     *
     * @return bool
     */
    public function cancelled()
    {
        return !is_null($this->ends_at);
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod()
    {
        $endsAt = new \DateTime($this->ends_at);

        if (!is_null($endsAt)) {
            return Carbon::now()->lt(Carbon::instance($endsAt));
        } else {
            return false;
        }
    }

    /**
     * Increment the quantity of the subscription.
     *
     * @param  int  $count
     *
     * @return $this
     */
    public function incrementQuantity($count = 1)
    {
        $this->updateQuantity($this->quantity + $count);

        return $this;
    }

    /**
     *  Increment the quantity of the subscription, and invoice immediately.
     *
     * @param  int  $count
     *
     * @return $this
     */
    public function incrementAndInvoice($count = 1)
    {
        $this->incrementQuantity($count);

        $this->user->invoice();

        return $this;
    }

    /**
     * Decrement the quantity of the subscription.
     *
     * @param  int  $count
     *
     * @return $this
     */
    public function decrementQuantity($count = 1)
    {
        $this->updateQuantity(max(1, $this->quantity - $count));

        return $this;
    }

    /**
     * Update the quantity of the subscription.
     *
     * @param  int  $quantity
     * @param  \Stripe\Customer|null  $customer
     *
     * @return $this
     */
    public function updateQuantity($quantity, $customer = null)
    {
        $subscription = $this->asStripeSubscription();

        $subscription->quantity = $quantity;

        $subscription->save();

        $this->quantity = $quantity;

        $this->save();

        return $this;
    }

    /**
     * Indicate that the plan change should not be prorated.
     *
     * @return $this
     */
    public function noProrate()
    {
        $this->prorate = false;

        return $this;
    }

    /**
     * Change the billing cycle anchor on a plan change.
     *
     * @param  int|string  $date
     *
     * @return $this
     */
    public function anchorBillingCycleOn($date = 'now')
    {
        if ($date instanceof DateTimeInterface) {
            $date = $date->getTimestamp();
        }

        $this->billingCycleAnchor = $date;

        return $this;
    }

    /**
     * Swap the subscription to a new Stripe plan.
     *
     * @param  string  $plan
     *
     * @return $this
     */
    public function swap($plan)
    {
        $subscription = $this->asStripeSubscription();

        $subscription->plan = $plan;

        $subscription->prorate = $this->prorate;

        $subscription->cancel_at_period_end = false;

        if (!is_null($this->billingCycleAnchor)) {
            $subscription->billingCycleAnchor = $this->billingCycleAnchor;
        }

        // If no specific trial end date has been set, the default behavior should be
        // to maintain the current trial state, whether that is "active" or to run
        // the swap out with the exact number of days left on this current plan.
        if ($this->onTrial()) {
            $subscription->trial_end = strtotime($this->trial_ends_at);
        } else {
            $subscription->trial_end = 'now';
        }

        // Again, if no explicit quantity was set, the default behaviors should be to
        // maintain the current quantity onto the new plan. This is a sensible one
        // that should be the expected behavior for most developers with Stripe.
        if ($this->quantity) {
            $subscription->quantity = $this->quantity;
        }

        $subscription->save();

        $this->updateOrFail([
            'stripe_plan' => $plan,
            'ends_at' => null,
        ]);

        return $this;
    }

    /**
     * Cancel the subscription at the end of the billing period.
     *
     * @return $this
     */
    public function cancel()
    {
        $subscription = $this->asStripeSubscription();
        $subscription->cancel_at_period_end = true;
        $subscription->save();

        $this->markAsCancelled();

        return $this;
    }

    /**
     * Reactivate the subscription at the end of the billing period.
     *
     * @return $this
     */
    public function reactivate()
    {
        $subscription = $this->asStripeSubscription();
        $subscription->cancel_at_period_end = false;
        $subscription->save();

        return $this;
    }

    /**
     * Cancel the subscription immediately.
     *
     * @return $this
     */
    public function cancelNow()
    {
        $subscription = $this->asStripeSubscription();
        $subscription->cancel();

        $this->markAsCancelled();

        return $this;
    }

    /**
     * Mark the subscription as cancelled.
     *
     * @return void
     */
    public function markAsCancelled()
    {
        $this->updateOrFail(['ends_at' => Carbon::now()->toDateTimeString()]);
    }

    /**
     * Resume the cancelled subscription.
     *
     * @return $this
     *
     * @throws \LogicException
     */
    public function resume()
    {
        if (!$this->onGracePeriod()) {
            throw new LogicException('Unable to resume subscription that is not within grace period.');
        }

        $subscription = $this->asStripeSubscription();

        $subscription->cancel_at_period_end = false;

        // To resume the subscription we need to set the plan parameter on the Stripe
        // subscription object. This will force Stripe to resume this subscription
        // where we left off. Then, we'll set the proper trial ending timestamp.
        $subscription->plan = $this->stripe_plan;

        if ($this->onTrial()) {
            $subscription->trial_end = strtotime($this->trial_ends_at);
        } else {
            $subscription->trial_end = 'now';
        }
        $subscription->save();

        // Finally, we will remove the ending timestamp from the user's record in the
        // local database to indicate that the subscription is active again and is
        // no longer "cancelled". Then we will save this record in the database.
        $this->updateOrFail(['ends_at' => null]);

        return $this;
    }

    /**
     * Sync the tax percentage of the user to the subscription.
     *
     * @return void
     */
    public function syncTaxPercentage()
    {
        $subscription = $this->asStripeSubscription();
        $subscription->tax_percent = $this->user->taxPercentage();
        $subscription->save();
    }

    /**
     * Get the subscription as a Stripe subscription object.
     *
     * @return Subscription
     */
    public function asStripeSubscription() : StripeSubscription
    {
        return $this->companyGroup->getStripeCustomerInfo()->subscriptions->retrieve($this->stripe_id);
    }
}
