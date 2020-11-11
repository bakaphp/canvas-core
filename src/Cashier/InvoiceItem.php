<?php

namespace Canvas\Cashier;

class InvoiceItem
{
    /**
     * The user instance.
     *
     */
    protected $user;

    /**
     * The Stripe invoice item instance.
     *
     * @var \Stripe\InvoiceItem
     */
    protected $item;

    /**
     * Create a new invoice item instance.
     *
     * @return void
     */
    public function __construct($user, $item)
    {
        $this->user = $user;
        $this->item = $item;
    }

    /**
     * Get the total for the line item.
     *
     * @return string
     */
    public function total()
    {
        return $this->formatAmount($this->amount);
    }

    /**
     * Get a human readable date for the start date.
     *
     * @return string
     */
    public function startDate()
    {
        if ($this->isSubscription()) {
            return date('M j, Y', $this->item->period->start);
        }
    }

    /**
     * Get a human readable date for the end date.
     *
     * @return string
     */
    public function endDate()
    {
        if ($this->isSubscription()) {
            return date('M j, Y', $this->item->period->end);
        }
    }

    /**
     * Determine if the invoice item is for a subscription.
     *
     * @return bool
     */
    public function isSubscription()
    {
        return $this->item->type === 'subscription';
    }

    /**
     * Format the given amount into a string based on the user's preferences.
     *
     * @param  int  $amount
     *
     * @return string
     */
    protected function formatAmount($amount)
    {
        return Cashier::formatAmount($amount);
    }

    /**
     * Get the underlying Stripe invoice item.
     *
     * @return \Stripe\StripeObject
     */
    public function asStripeInvoiceItem()
    {
        return $this->item;
    }

    /**
     * Dynamically access the Stripe line item instance.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->item->{$key};
    }
}
