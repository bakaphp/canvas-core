<?php

declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Cashier\Cashier;
use Stripe\SubscriptionItem as StripeSubscriptionItem;

class SubscriptionItems extends AbstractModel
{
    public int $subscription_id;
    public string $stripe_id;
    public string $stripe_plan;
    public int $quantity = 1;

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('subscription_items');

        $this->belongsTo(
            'subscription_id',
            Subscription::class,
            'id',
            [
                'alias' => 'subscription'
            ]
        );

        $this->belongsTo(
            'apps_plans_id',
            AppsPlans::class,
            'id',
            ['alias' => 'appPlan']
        );
    }

    /**
     * Increment quantity.
     *
     * @param int $count
     *
     * @return self
     */
    public function incrementQuantity(int $count = 1) : self
    {
        $this->updateQuantity($this->quantity + $count);

        return $this;
    }

    /**
     * Decrease quantity.
     *
     * @param int $count
     *
     * @return self
     */
    public function decrementQuantity(int $count = 1) : self
    {
        $this->updateQuantity(max(1, $this->quantity - $count));

        return $this;
    }

    /**
     * Update quantity.
     *
     * @param int $quantity
     *
     * @return self
     */
    public function updateQuantity(int $quantity) : self
    {
        $stripeSubscriptionItem = $this->asStripeSubscriptionItem();
        $stripeSubscriptionItem->quantity = $quantity;
        $stripeSubscriptionItem->save();

        $this->quantity = $quantity;
        $this->save();

        if ($this->subscription->hasSinglePlan()) {
            $this->subscription->quantity = $quantity;
            $this->subscription->save();
        }

        return $this;
    }

    /**
     * Swap item for a new app plan.
     *
     * @param AppsPlans $plan
     * @param array $options
     *
     * @return self
     */
    public function swap(AppsPlans $plan, array $options = []) : self
    {
        $options = array_merge([
            'plan' => $plan->stripe_plan,
            'quantity' => $this->quantity,
        ], $options);

        $item = StripeSubscriptionItem::update(
            $this->stripe_id,
            $options,
            Cashier::stripeOptions()
        );

        $this->updateOrFail([
            'stripe_plan' => $plan->stripe_plan,
            'quantity' => $item->quantity,
        ]);

        if ($this->subscription->hasSinglePlan()) {
            $this->subscription->updateOrFail([
                'stripe_plan' => $plan->stripe_plan,
                'quantity' => $item->quantity,
            ])->save();
        }

        return $this;
    }

    /**
     * Update the underlying Stripe subscription item information for the model.
     *
     * @param  array  $options
     *
     * @return StripeSubscriptionItem
     */
    public function updateStripeSubscriptionItem(array $options = []) : StripeSubscriptionItem
    {
        return StripeSubscriptionItem::update(
            $this->stripe_id,
            $options,
            Cashier::stripeOptions()
        );
    }

    /**
     * Get the subscription as a Stripe subscription item object.
     *
     * @param  array  $expand
     *
     * @return StripeSubscriptionItem
     */
    public function asStripeSubscriptionItem(array $expand = []) : StripeSubscriptionItem
    {
        return StripeSubscriptionItem::retrieve(
            ['id' => $this->stripe_id, 'expand' => $expand],
            Cashier::stripeOptions()
        );
    }
}
