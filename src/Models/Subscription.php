<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Cashier\Cashier;
use Canvas\Cashier\Exceptions\Subscriptions as SubscriptionException;
use Carbon\Carbon;
use DateTime;
use DateTimeInterface;
use Exception;
use LogicException;
use Phalcon\Db\RawValue;
use Phalcon\Di;
use Stripe\Subscription as StripeSubscription;

class Subscription extends AbstractModel
{
    const DEFAULT_GRACE_PERIOD_DAYS = 5;

    public int $users_id;
    public int $companies_groups_id;
    public int $apps_id;
    public ?string $name = null;
    public string $stripe_id;
    public string $stripe_plan;
    public ?string $stripe_status;
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

        $this->belongsTo('users_id', 'Canvas\Models\Users', 'id', ['alias' => 'user']);

        $this->belongsTo(
            'companies_groups_id',
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

        $this->hasMany(
            'id',
            SubscriptionItems::class,
            'subscription_id',
            [
                'alias' => 'plans'
            ]
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
                $user->getDefaultCompany()->getDefaultCompanyGroup()->getId(),
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

        return (bool) $this->is_active &&
            $this->stripe_status !== StripeSubscription::STATUS_INCOMPLETE &&
            $this->stripe_status !== StripeSubscription::STATUS_INCOMPLETE_EXPIRED &&
            $this->stripe_status !== StripeSubscription::STATUS_UNPAID;
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
     * @return bool
     */
    public function markAsActivate() : bool
    {
        $this->is_active = 1;
        $this->paid = 1;
        //$this->grace_period_ends = new RawValue('NULL');
        $this->ends_at = null; //new rawValue('NULL');
        $this->next_due_payment = $this->ends_at;
        $this->is_cancelled = 0;
        return $this->update();
    }

    /**
     * @deprecated v0.3
     */
    public function activate() : bool
    {
        return $this->markAsActivate();
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
    public function valid() : bool
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is no longer active.
     *
     * @return bool
     */
    public function cancelled() : bool
    {
        return !is_null($this->ends_at) && $this->is_cancelled;
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod() : bool
    {
        $endsAt = new DateTime((string) $this->ends_at);

        if (!is_null($endsAt)) {
            return Carbon::now()->lt(Carbon::instance($endsAt));
        }

        return false;
    }

    /**
     * Increment the quantity of the subscription.
     *
     * @param  int  $count
     *
     * @return self
     */
    public function incrementQuantity($count = 1) : self
    {
        $this->updateQuantity($this->quantity + $count);

        return $this;
    }

    /**
     *  Increment the quantity of the subscription, and invoice immediately.
     *
     * @param  int  $count
     *
     * @return self
     */
    public function incrementAndInvoice($count = 1) : self
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
     * @return self
     */
    public function decrementQuantity($count = 1) : self
    {
        $this->updateQuantity(max(1, $this->quantity - $count));

        return $this;
    }

    /**
     * Update the quantity of the subscription.
     *
     * @param  int  $quantity
     *
     * @return self
     */
    public function updateQuantity($quantity) : self
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
     * @return self
     */
    public function noProrate() : self
    {
        $this->prorate = false;

        return $this;
    }

    /**
     * Change the billing cycle anchor on a plan change.
     *
     * @param  int|string  $date
     *
     * @return self
     */
    public function anchorBillingCycleOn($date = 'now') : self
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
     * @param  AppsPlans  $plan
     *
     * @return self
     */
    public function swap(AppsPlans $plan) : self
    {
        $subscription = $this->asStripeSubscription();

        $stripeSubscription = StripeSubscription::update(
            $this->stripe_id,
            $this->getSwapOptions($plan),
            Cashier::stripeOptions()
        );

        $this->updateOrFail([
            'stripe_status' => $stripeSubscription->status,
            'stripe_plan' => $stripeSubscription->plan ? $stripeSubscription->plan->id : count($stripeSubscription->items) . ' items',
            'quantity' => $stripeSubscription->quantity ?: count($stripeSubscription->items),
            'name' => $plan->name,
            'ends_at' => null,
        ]);

        $this->plans->delete();
        foreach ($stripeSubscription->items as $item) {
            $subscriptionItem = new SubscriptionItems();
            $subscriptionItem->subscription_id = $this->getId();
            $subscriptionItem->apps_plans_id = $item->metadata->appPlan ?: 0;
            $subscriptionItem->stripe_id = $item->id;
            $subscriptionItem->stripe_plan = $item->plan->id;
            $subscriptionItem->quantity = $item->quantity;
            $subscriptionItem->saveOrFail();
        }

        return $this;
    }

    /**
     * Add plan to the current entity.
     */
    public function addPlan(AppsPlans $plan, int $quantity = 1) : self
    {
        if ($this->countPlans('apps_plans_id =' . $plan->getId())) {
            throw SubscriptionException::duplicatePlan($plan);
        }

        $subscription = $this->asStripeSubscription();

        $item = $subscription->items->create(array_merge([
            'plan' => $plan->stripe_id,
            'quantity' => $quantity,
            'metadata' => [
                'appPlan' => $plan->getId()
            ]
        ]));

        $subscriptionItem = new SubscriptionItems();
        $subscriptionItem->subscription_id = $this->getId();
        $subscriptionItem->apps_plans_id = $item->metadata->appPlan ?: 0;
        $subscriptionItem->stripe_id = $item->id;
        $subscriptionItem->stripe_plan = $item->plan->id;
        $subscriptionItem->quantity = $item->quantity;
        $subscriptionItem->saveOrFail();

        return $this;
    }

    /**
     * Remove a plan from a subscription.
     *
     * @param AppsPlans $plan
     *
     * @return self
     */
    public function removePlan(AppsPlans $plan) : self
    {
        if ($this->hasSinglePlan()) {
            throw new SubscriptionException('Cant delete single plan for this subscription');
        }

        if (!$item = $this->getPlans('apps_plans_id = ' . $plan->getId())) {
            throw new Exception('Plan Id ' . $plan->getId() . ' not found on this subscription');
        }

        $item->getFirst()->asStripeSubscriptionItem()->delete();

        $item->getFirst()->deleteOrFail();

        if ($this->plans->count() < 2) {
            $item = $this->plans->getFirst();

            $this->updateOrFail([
                'stripe_plan' => $item->stripe_plan,
                'quantity' => $item->quantity,
            ]);
        }

        return $this;
    }

    /**
     * Get the options array for a swap operation.
     *
     * @param AppsPlans $items
     * @param array $options
     *
     * @return array
     */
    protected function getSwapOptions(AppsPlans $plan, array $options = []) : array
    {
        //replace the id of the plan with this new one

        $payload = [
            'items' => [
                [
                    'id' => $this->getPlans('apps_plans_id > 0')->getFirst()->stripe_id,
                    'price' => $plan->stripe_id,
                    'metadata' => [
                        'appPlan' => $plan->getId()
                    ]
                ]
            ],
            'expand' => ['latest_invoice.payment_intent'],
        ];

        $payload = array_merge($payload, $options);

        return $payload;
    }

    /**
     * Cancel the subscription at the end of the billing period.
     *
     * @return self
     */
    public function cancel() : self
    {
        $subscription = $this->asStripeSubscription();
        $subscription->cancel_at_period_end = true;
        $subscription->save();

        $this->stripe_status = $subscription->status;

        // If the user was on trial, we will set the grace period to end when the trial
        // would have ended. Otherwise, we'll retrieve the end of the billing period
        // period and make that the end of the grace period for this current user.
        if ($this->onTrial()) {
            $this->ends_at = $this->trial_ends_at;
        } else {
            $this->ends_at = Carbon::createFromTimestamp(
                $subscription->current_period_end
            );
        }

        $this->save();

        return $this;
    }

    /**
     * Reactivate the subscription at the end of the billing period.
     *
     * @return self
     */
    public function reactivate() : self
    {
        $subscription = $this->asStripeSubscription();
        $subscription->cancel_at_period_end = false;
        $subscription->save();

        return $this;
    }

    /**
     * Cancel the subscription immediately.
     *
     * @return self
     */
    public function cancelNow() : self
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
    public function markAsCancelled() : void
    {
        $this->ends_at = Carbon::now()->toDateTimeString();
        $this->is_cancelled = 1;
        $this->is_active = 0;
        $this->stripe_status = StripeSubscription::STATUS_CANCELED;
        $this->updateOrFail();
    }

    /**
     * Resume the cancelled subscription.
     *
     * @return self
     *
     * @throws LogicException
     */
    public function resume() : self
    {
        if (!$this->onGracePeriod()) {
            throw new LogicException('Unable to resume subscription that is not within grace period.');
        }

        $subscription = $this->asStripeSubscription();

        $subscription->cancel_at_period_end = false;

        if ($this->onTrial()) {
            $subscription->trial_end = strtotime($this->trial_ends_at);
        } else {
            $subscription->trial_end = 'now';
        }
        $subscription->save();

        $this->markAsActivate();

        // Finally, we will remove the ending timestamp from the user's record in the
        // local database to indicate that the subscription is active again and is
        // no longer "cancelled". Then we will save this record in the database.
        $this->updateOrFail([
            'ends_at' => null,
            'stripe_status' => $subscription->status,
        ]);

        return $this;
    }

    /**
     * Sync the tax percentage of the user to the subscription.
     *
     * @return void
     */
    public function syncTaxPercentage() : void
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

    /**
     * Determine if the subscription has this plan.
     *
     * @param AppsPlans $plan
     *
     * @return bool
     */
    public function hasPlan(AppsPlans $plan) : bool
    {
        return $this->countPlans([
            'conditions' => 'stripe_plan = :stripe_plan:',
            'bind' => [
                'stripe_plan' => $plan->stripe_plan
            ]
        ]) > 0;
    }

    /**
     * Determine if the subscription has multiple plans.
     *
     * @return bool
     */
    public function hasMultiplePlans() : bool
    {
        return count($this->plans) > 1;
    }

    /**
     * Determine if the subscription has a single plan.
     *
     * @return bool
     */
    public function hasSinglePlan() : bool
    {
        return !$this->hasMultiplePlans();
    }

    /**
     * Determine if the subscription is incomplete.
     *
     * @return bool
     */
    public function incomplete() : bool
    {
        return $this->stripe_status === StripeSubscription::STATUS_INCOMPLETE;
    }

    /**
     * Determine if the subscription is past due.
     *
     * @return bool
     */
    public function pastDue() : bool
    {
        return $this->stripe_status === StripeSubscription::STATUS_PAST_DUE;
    }

    /**
     * Sync the Stripe status of the subscription.
     *
     * @return void
     */
    public function syncStripeStatus()
    {
        $subscription = $this->asStripeSubscription();

        $this->stripe_status = $subscription->status;

        $this->save();
    }

    /**
     * Get the active subscription for this company app.
     *
     * @return Subscription
     */
    public static function getActiveForThisApp() : Subscription
    {
        return self::getByDefaultCompany(Di::getDefault()->get('userData'));
    }
}
