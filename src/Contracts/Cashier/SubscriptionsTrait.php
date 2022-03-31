<?php

declare(strict_types=1);

namespace Canvas\Contracts\Cashier;

use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Cashier\SubscriptionBuilder;
use Canvas\Models\AppsPlans;
use Canvas\Models\Subscription;
use Phalcon\Di;

trait SubscriptionsTrait
{
    /**
     * Create a new subscription.
     *
     * @param string $name
     * @param string $plan
     *
     * @return void
     */
    public function newSubscription(AppsPlans $appPlan) : SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $appPlan, Di::getDefault()->get('app'));
    }

    /**
     * Get the subscription object from this entity.
     * Based on the entity relationship.
     *
     * @return Subscription
     */
    public function subscription() : Subscription
    {
        $subscription = $this->subscription;

        if (!$subscription) {
            throw new InternalServerErrorException('No Active Subscription for Company Group ' . $this->getId());
        }

        return $subscription;
    }

    /**
     * Determine if the entity is subscribed.
     *
     * @return bool
     */
    public function subscribed() : bool
    {
        $subscription = $this->subscription();

        if (!$subscription->valid()) {
            return false;
        }

        return true;
    }

    /**
     * Are you subscript to this plan?
     *
     * @param AppsPlans $appPlan
     *
     * @return bool
     */
    public function subscribedToPlan(AppsPlans $appPlan) : bool
    {
        $subscription = $this->subscription();

        return $subscription->hasPlan($appPlan);
    }

    /**
     * Is this subscription on a Freetrial?
     *
     * @return bool
     */
    public function onTrial() : bool
    {
        $subscription = $this->subscription();

        if (!$subscription || !$subscription->onTrial()) {
            return false;
        }

        return true;
    }

    /**
     * Date the subscription ends at.
     *
     * @return string
     */
    public function trialEndsAt() : string
    {
        return $this->subscription()->trial_ends_at;
    }

    /**
     * Get the tax percentage to apply to the subscription.
     *
     * @return int|float
     *
     * @deprecated Please migrate to the new Tax Rates API.
     */
    public function taxPercentage() : int
    {
        return 0;
    }

    /**
     * Get the tax rates to apply to the subscription.
     *
     * @return array
     */
    public function taxRates() : array
    {
        return [];
    }

    /**
     * Get the tax rates to apply to individual subscription items.
     *
     * @return array
     */
    public function planTaxRates() : array
    {
        return [];
    }
}
