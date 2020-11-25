<?php

namespace Canvas\Cashier;

use Canvas\Models\Apps;
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

    /**
     * Create a new Subscription.
     *
     * @param ModelInterface $entity
     * @param string $name
     * @param string $plan
     * @param Apps $apps
     */
    public function __construct(ModelInterface $entity, string $name, string $plan, Apps $apps)
    {
        $this->entity = $entity;
        $this->name = $name;
        $this->apps = $apps;
        $this->plan = $plan;

        $this->addPlan($this->plan);
    }

    /**
     * Stripe has change plans to prices, we will continue to use plans name since it a best use
     * to subscriptions.
     *
     * @param string $plan
     * @param int $quantity
     *
     * @return self
     */
    public function addPlan(string $plan, int $quantity = 1) : self
    {
        $options = [
            'price' => $plan,
            'quantity' => $quantity,
        ];

        if ($taxRates = $this->getPlanTaxRatesForPayload($plan)) {
            $options['tax_rates'] = $taxRates;
        }

        $this->plans[$plan] = $options;

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
        $subscription->quantity = $stripeSubscription->quantity;
        $subscription->trial_ends_at = $trialEndsAt->toDateTimeString();
        $subscription->companies_groups_id = $this->entity->getId();
        $subscription->apps_id = $this->apps->getId();
        $subscription->apps_plans_id = $this->apps->getDefaultPlan()->getId();
        $subscription->payment_frequency_id = $this->apps->getDefaultPlan()->payment_frequencies_id;
        $subscription->is_freetrial = $trialEndsAt ? 1 : 0;
        $subscription->is_active = 1;
        $subscription->saveOFail();

        foreach ($stripeSubscription->items as $item) {
            $subscriptionItem = new SubscriptionItems();
            $subscriptionItem->subscription_id = $subscription->getId();
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
        return array_filter([
            'items' => $this->plans,
            'coupon' => $this->coupon,
            'trial_end' => $this->getTrialEndForPayload(),
            'tax_percent' => $this->getTaxPercentageForPayload(),
            'metadata' => $this->metadata,
        ]);
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
    }
}
