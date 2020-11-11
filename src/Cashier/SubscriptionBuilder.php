<?php

namespace Canvas\Cashier;

use Canvas\Models\Apps;
use Canvas\Models\Companies;
use Canvas\Models\Subscription;
use Carbon\Carbon;
use Phalcon\Mvc\Model;

class SubscriptionBuilder
{
    /**
     * The user model that is subscribing.
     *
     */
    protected $user;

    /**
     * The name of the subscription.
     *
     * @var string
     */
    protected $name;

    /**
     * The name of the plan being subscribed to.
     *
     * @var string
     */
    protected $plan;

    /**
     * The quantity of the subscription.
     *
     * @var int
     */
    protected $quantity = 1;

    /**
     * The number of trial days to apply to the subscription.
     *
     * @var int|null
     */
    protected $trialDays;

    /**
     * Indicates that the trial should end immediately.
     *
     * @var bool
     */
    protected $skipTrial = false;

    /**
     * The coupon code being applied to the customer.
     *
     * @var string|null
     */
    protected $coupon;

    /**
     * The metadata to apply to the subscription.
     *
     * @var array|null
     */
    protected $metadata;

    protected Companies $company;

    /**
     * App.
     *
     * @var Apps
     */
    protected $apps;

    /**
     * Active Subscription Id.
     *
     * @var string
     */
    protected $activeSubscriptionId;

    /**
     * Create a new subscription builder instance.
     *
     * @param  mixed  $user
     * @param  string  $name
     * @param  string  $plan
     *
     * @return void
     */
    public function __construct($user, $name, $plan, Companies $company, Apps $apps)
    {
        $this->user = $user;
        $this->name = $name;
        $this->plan = $plan;
        $this->company = $company;
        $this->apps = $apps;
    }

    /**
     * Specify the quantity of the subscription.
     *
     * @param  int  $quantity
     *
     * @return $this
     */
    public function quantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Specify the ending date of the trial.
     *
     * @param  int  $trialDays
     *
     * @return $this
     */
    public function trialDays($trialDays)
    {
        $this->trialDays = $trialDays;

        return $this;
    }

    /**
     * Force the trial to end immediately.
     *
     * @return $this
     */
    public function skipTrial()
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
    public function withCoupon($coupon)
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
    public function withMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Add a new Stripe subscription to the user.
     *
     * @param  array  $options
     *
     * @return \Laravel\Cashier\Subscription
     */
    public function add(array $options = [])
    {
        return $this->create(null, $options);
    }

    /**
     * Create a new Stripe subscription.
     *
     * @param  string|null  $token
     * @param  array  $options
     *
     * @return Subscription
     */
    public function create($token = null, array $options = [])
    {
        $customer = $this->getStripeCustomer($token, $options);

        $subscription = $customer->subscriptions->create($this->buildPayload());

        if ($this->skipTrial) {
            $trialEndsAt = null;
        } else {
            $object = Carbon::now()->addDays($this->trialDays);
            $trialEndsAt = $this->trialDays ? $object->toDateTimeString() : null;
        }

        $object = new Subscription();
        $object->name = $this->name;
        $object->stripe_id = $subscription->id;
        $object->stripe_plan = $this->plan;
        $object->quantity = $this->quantity;
        $object->trial_ends_at = $trialEndsAt;
        $object->companies_id = $this->company->getId();
        $object->apps_id = $this->apps->getId();

        //Need call it before save relationship
        $this->user->subscriptions();
        $this->user->subscriptions = $object;
        $this->user->active_subscription_id = $subscription->id;

        $this->user->saveOrFail();

        return $this->user;
    }

    /**
     * Get the Stripe customer instance for the current user and token.
     *
     * @param  string|null  $token
     * @param  array  $options
     *
     * @return \Stripe\Customer
     */
    protected function getStripeCustomer($token = null, array $options = [])
    {
        if (!$this->user->stripe_id) {
            $customer = $this->user->createAsStripeCustomer(
                $token,
                array_merge($options, array_filter(['coupon' => $this->coupon]))
            );
        } else {
            $customer = $this->user->asStripeCustomer();

            if ($token) {
                $this->user->updateCard($token);
            }
        }

        return $customer;
    }

    /**
     * Build the payload for subscription creation.
     *
     * @return array
     */
    protected function buildPayload()
    {
        return array_filter([
            'plan' => $this->plan,
            'quantity' => $this->quantity,
            'coupon' => $this->coupon,
            'trial_end' => $this->getTrialEndForPayload(),
            'tax_percent' => $this->getTaxPercentageForPayload(),
            'metadata' => $this->metadata,
        ]);
    }

    /**
     * Get the trial ending date for the Stripe payload.
     *
     * @return int|null
     */
    protected function getTrialEndForPayload()
    {
        if ($this->skipTrial) {
            return 'now';
        }

        if ($this->trialDays) {
            return Carbon::now()->addDays($this->trialDays)->getTimestamp();
        }
    }

    /**
     * Get the tax percentage for the Stripe payload.
     *
     * @return int|null
     */
    protected function getTaxPercentageForPayload()
    {
        if ($taxPercentage = $this->user->taxPercentage()) {
            return $taxPercentage;
        }
    }
}
