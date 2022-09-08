<?php

namespace Canvas\Cashier;

use Canvas\Contracts\Cashier\CustomerTrait;
use Canvas\Contracts\Cashier\PaymentMethodsTrait;
use Canvas\Contracts\Cashier\PaymentsTrait;
use Canvas\Contracts\Cashier\SubscriptionsTrait;
use Canvas\Models\AppsPlans;
use Canvas\Models\Companies;
use Canvas\Models\CompaniesBranches;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\Subscription;
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
    public function startFreeTrial(
        CompaniesGroups $companyGroup,
        Companies $company,
        CompaniesBranches $branch,
        array $options = [],
        array $customerOptions = []
    ) : Subscription {
        $defaultPlan = AppsPlans::getDefaultPlan();

        return $this->newSubscription(
            $companyGroup,
            $company,
            $branch,
            $defaultPlan,
        )
            ->trialDays($defaultPlan->free_trial_dates)
            ->withMetadata(['appPlan' => $defaultPlan->getId()])
            ->create($options, $customerOptions);
    }
}
