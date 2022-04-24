<?php

declare(strict_types=1);

namespace Canvas\Cashier;

use Canvas\Enums\State;
use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\Companies;
use Canvas\Models\CompaniesBranches;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\Subscription;
use Canvas\Models\SubscriptionItems;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use InvalidArgumentException;
use Phalcon\Helper\Arr;
use Phalcon\Mvc\ModelInterface;
use Stripe\Subscription as StripeSubscription;

class SubscriptionBuilder
{
    /**
     * Entity who is been assigned a subscription.
     */
    protected ModelInterface $entity;
    protected CompaniesGroups $companyGroup;
    protected Companies $company;
    protected CompaniesBranches $branch;

    /**
     * The name of the subscription.
     */
    protected string $name;

    /**
     * The name of the plans being subscribed to.
     */
    protected string $plan;
    protected array $plans = [];

    /**
     * The quantity of the subscription.
     *
     * @var int
     */
    protected int $quantity = 1;

    /**
     * Date the free trial ends at.
     */
    protected ?CarbonInterface $trialEndsAt = null;

    /**
     * Indicates that the trial should end immediately.
     */
    protected bool $skipTrial = false;

    /**
     * The coupon code being applied to the customer.
     */
    protected ?string $coupon = null;

    /**
     * The metadata to apply to the subscription.
     */
    protected array $metadata = [];

    protected Apps $apps;
    protected AppsPlans $appsPlan;

    /**
     * Create a new Subscription.
     *
     * @param Companies $entity
     * @param string $name
     * @param string $plan
     * @param Apps $apps
     */
    public function __construct(
        ModelInterface $entity,
        AppsPlans $appPlan,
        Apps $apps,
        CompaniesGroups $companyGroup,
        Companies $company,
        CompaniesBranches $branch
    ) {
        $this->entity = $entity;
        $this->name = $appPlan->name;
        $this->apps = $apps;
        $this->plan = $appPlan->stripe_id;
        $this->appsPlan = $appPlan;
        $this->companyGroup = $companyGroup;
        $this->company = $company;
        $this->branch = $branch;

        $this->addPlan($appPlan);
    }

    /**
     * Stripe has change plans to prices, we will continue to use plans name since it a best use
     * to subscriptions.
     *
     * @param AppsPlans $plan
     * @param int $quantity
     *
     * @return self
     */
    public function addPlan(AppsPlans $plan, int $quantity = 1) : self
    {
        $planStripeId = $plan->stripe_id;
        $options = [
            'price' => $planStripeId,
            'quantity' => $quantity,
            'metadata' => [
                'appPlan' => $plan->getId()
            ]
        ];

        if ($taxRates = $this->getPlanTaxRatesForPayload($planStripeId)) {
            $options['tax_rates'] = $taxRates;
        }

        $this->plans[$planStripeId] = $options;

        return $this;
    }

    /**
     * Specify the quantity of the subscription.
     *
     * @param  int  $quantity
     *
     * @return $this
     */
    public function quantity(int $quantity, ?string $plan = null) : self
    {
        $this->quantity = $quantity;

        return $this;

        if (is_null($plan)) {
            if (count($this->plans) > 1) {
                throw new InvalidArgumentException('Plan is required when creating multi-plan subscriptions.');
            }

            $plan = Arr::first($this->plans)['price'];
        }

        return $this->addPlan($plan, $quantity);
    }

    /**
     * Specify the ending date of the trial.
     *
     * @param  int $trialDays
     *
     * @return self
     */
    public function trialDays(int $trialDays) : self
    {
        $this->trialEndsAt = Carbon::now()->addDays($trialDays);

        return $this;
    }

    /**
     * Specify the date.
     *
     * @param CarbonInterface $trialUntil
     *
     * @return self
     */
    public function trialUntil(CarbonInterface $trialUntil) : self
    {
        $this->trialEndsAt = $trialUntil;

        return $this;
    }

    /**
     * Force the trial to end immediately.
     *
     * @return $this
     */
    public function skipTrial() : self
    {
        $this->skipTrial = true;

        return $this;
    }

    /**
     * The coupon to apply to a new subscription.
     *
     * @param  string  $coupon
     *
     * @return $this
     */
    public function withCoupon(string $coupon) : self
    {
        $this->coupon = $coupon;

        return $this;
    }

    /**
     * The metadata to apply to a new subscription.
     *
     * @param  array  $metadata
     *
     * @return $this
     */
    public function withMetadata(array $metadata) : self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Add a new Stripe subscription to the user.
     *
     * @param  array  $options
     *
     * @return Subscription
     */
    public function add(array $options = []) : Subscription
    {
        return $this->create($options);
    }

    /**
     * Create a new Stripe subscription.
     *
     * @param array $options
     * @param array $customerOptions
     *
     * @return Subscription
     */
    public function create(array $options = [], array $customerOptions = []) : Subscription
    {
        $customer = $this->entity->createOrGetStripeCustomerInfo($customerOptions);

        $payload = array_merge(
            [
                'customer' => $customer->id
            ],
            $this->buildPayload(),
            $options
        );

        $stripeSubscription = StripeSubscription::create(
            $payload,
            Cashier::stripeOptions()
        );

        if ($this->skipTrial) {
            $trialEndsAt = null;
        } else {
            $trialEndsAt = $this->trialEndsAt;
        }

        $subscription = new Subscription();
        $subscription->name = $this->name;
        $subscription->stripe_id = $stripeSubscription->id;
        $subscription->stripe_plan = $stripeSubscription->plan ? $stripeSubscription->plan->id : null;
        $subscription->stripe_status = $stripeSubscription->status;
        $subscription->quantity = $stripeSubscription->quantity;
        $subscription->trial_ends_at = $trialEndsAt->toDateTimeString();
        $subscription->companies_id = $this->company->getId();
        $subscription->companies_branches_id = $this->branch->getId();
        $subscription->companies_groups_id = $this->companyGroup->getId();
        $subscription->apps_id = $this->apps->getId();
        $subscription->payment_frequency_id = $this->apps->getDefaultPlan()->payment_frequencies_id;
        $subscription->subscription_types_id = $this->apps->subscription_types_id;
        $subscription->is_freetrial = $trialEndsAt ? State::YES : State::NO;
        $subscription->users_id = $this->entity->users_id;
        $subscription->is_active = State::YES;
        $subscription->saveOrFail();

        foreach ($stripeSubscription->items as $item) {
            $subscriptionItem = new SubscriptionItems();
            $subscriptionItem->subscription_id = $subscription->getId();
            $subscriptionItem->apps_plans_id = $this->appsPlan->getId();
            $subscriptionItem->stripe_id = $item->id;
            $subscriptionItem->stripe_plan = $item->plan->id;
            $subscriptionItem->quantity = $item->quantity;
            $subscriptionItem->saveOrFail();
        }

        return $subscription;
    }

    /**
     * Build the payload for subscription creation.
     *
     * @return array
     */
    protected function buildPayload() : array
    {
        $payload = array_filter([
            'items' => array_values($this->plans),
            'coupon' => $this->coupon,
            'trial_end' => $this->getTrialEndForPayload(),
            'tax_percent' => $this->getTaxPercentageForPayload(),
            'metadata' => $this->metadata,
        ]);

        if ($taxRates = $this->getTaxRatesForPayload()) {
            $payload['default_tax_rates'] = $taxRates;
        } elseif ($taxPercentage = $this->getTaxPercentageForPayload()) {
            $payload['tax_percent'] = $taxPercentage;
        }

        return $payload;
    }

    /**
     * Get the trial ending date for the Stripe payload.
     *
     * @return int|string|null
     */
    protected function getTrialEndForPayload()
    {
        if ($this->skipTrial) {
            return 'now';
        }

        if ($this->trialEndsAt) {
            return $this->trialEndsAt->getTimestamp();
        }
    }

    /**
     * Get the tax percentage for the Stripe payload.
     *
     * @return int|null
     */
    protected function getTaxPercentageForPayload() : ?int
    {
        if ($taxPercentage = $this->entity->taxPercentage()) {
            return $taxPercentage;
        }

        return null;
    }

    /**
     * Get the tax rates for the Stripe payload.
     *
     * @return array|null
     */
    protected function getTaxRatesForPayload() : ?array
    {
        if ($taxRates = $this->entity->taxRates()) {
            return $taxRates;
        }
        return null;
    }

    /**
     * Get the plan tax rates for the Stripe payload.
     *
     * @param  string  $plan
     *
     * @return array|null
     */
    protected function getPlanTaxRatesForPayload(string $plan) : ?array
    {
        if ($taxRates = $this->entity->planTaxRates()) {
            return $taxRates[$plan] ?? null;
        }

        return null;
    }
}
