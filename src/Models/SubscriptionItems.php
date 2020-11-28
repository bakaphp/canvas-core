<?php

namespace Canvas\Models;

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
}
