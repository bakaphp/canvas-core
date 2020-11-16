<?php

namespace Canvas\Cashier;

use Canvas\Contracts\Cashier\CustomerTrait;
use Canvas\Contracts\Cashier\PaymentMethodsTrait;
use Canvas\Contracts\Cashier\PaymentTrait;
use Canvas\Contracts\Cashier\SubscriptionsTrait;
use Phalcon\Di;

trait Billable
{
    use CustomerTrait;
    use SubscriptionsTrait;
    use PaymentTrait;
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
}
