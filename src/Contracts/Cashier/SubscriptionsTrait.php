<?php

namespace Canvas\Contracts\Cashier;

use Canvas\Cashier\SubscriptionBuilder;
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
    public function newSubscription(string $name, string $plan) : SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $name, $plan, Di::getDefault()->get('app'));
    }

    /**
     * Get the subscription object from this entity.
     * Based on the entity relationship.
     *
     * @return Subscription
     */
    public function subscription() : Subscription
    {
        return $this->subscription;
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
