<?php

namespace Canvas\Contracts\Cashier;

use Canvas\Cashier\Cashier;
use Phalcon\Di;
use Stripe\PaymentIntent as StripePaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund as StripeRefund;

trait PaymentsTrait
{
    /**
     * Single payment for a customer.
     *
     * @param int $amount
     * @param PaymentMethod $paymentMethod
     * @param array $options
     *
     * @return StripePaymentIntent
     */
    public function charge(int $amount, PaymentMethod $paymentMethod, array $options = []) : StripePaymentIntent
    {
        $options = array_merge([
            'confirmation_method' => 'automatic',
            'confirm' => true,
            'currency' => Di::getDefault()->get('app')->defaultCurrency(),
        ], $options);

        $options['amount'] = $amount;
        $options['payment_method'] = $paymentMethod;

        if ($this->hasStripeId()) {
            $options['customer'] = $this->stripe_id;
        }

        $payment = StripePaymentIntent::create($options, Cashier::stripeOptions());

        return $payment;
    }

    /**
     * Given the paymentIntent ID do a refund.
     *
     * @param string $paymentIntent
     * @param array $options
     *
     * @return StripeRefund
     */
    public function refund(string $paymentIntent, array $options = []) : StripeRefund
    {
        return StripeRefund::create(
            ['payment_intent' => $paymentIntent] + $options,
            Cashier::stripeOptions()
        );
    }
}
