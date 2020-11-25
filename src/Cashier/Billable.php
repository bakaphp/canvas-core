<?php

namespace Canvas\Cashier;

use Canvas\Contracts\Cashier\CustomerTrait;
use Canvas\Contracts\Cashier\PaymentMethodsTrait;
use Canvas\Contracts\Cashier\PaymentsTrait;
use Canvas\Contracts\Cashier\SubscriptionsTrait;
use Canvas\Models\AppsPlans;
use Phalcon\Di;

trait Billable
{
    use CustomerTrait;
    use SubscriptionsTrait;
    use PaymentsTrait;
    use PaymentMethodsTrait;

    /**
     * Get the Stripe API key.
     *
     * @return string
     */
    public static function getStripeKey() : string
    {
        $stripe = Di::getDefault()->get('config')->stripe;

        return $stripe->secretKey ?: getenv('STRIPE_SECRET');
    }

    /**
     * For the given entity using this trait start a free trial.
     *
     * @return SubscriptionBuilder
     */
    public function startFreeTrial() : SubscriptionBuilder
    {
        $defaultPlan = AppsPlans::getDefaultPlan();

        return $this->newSubscription(
            $defaultPlan->name,
            $defaultPlan->stripe_id,
        )
        ->trialDays($defaultPlan->free_trial_dates)
        ->create();
    }
}
