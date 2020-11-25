<?php

namespace Canvas\Contracts\Cashier;

use Canvas\Cashier\Cashier;
use Canvas\Cashier\Exceptions\Customer as CustomerException;
use Stripe\BillingPortal\Session as StripeBillingPortalSession;
use Stripe\Customer as StripeCustomer;

trait CustomerTrait
{
    /**
     * Get the stripe Id.
     *
     * @return string|null
     */
    public function getStripeId() : ?string
    {
        return $this->stripe_id;
    }

    /**
     * has stripe id?
     *
     * @return bool
     */
    public function hasStripeId() : bool
    {
        return !is_null($this->stripe_id);
    }

    /**
     * Determine if the customer exist.
     *
     * @return bool
     */
    protected function isCustomer() : bool
    {
        if (!$this->hasStripeId()) {
            throw CustomerException::notYetCreated($this);
        }

        return true;
    }

    /**
     * Get the email address used to create the customer in Stripe.
     *
     * @return string|null
     */
    public function stripeEmail() : string
    {
        if (property_exists($this, 'email')) {
            return $this->email;
        }

        if (is_object($this->users)) {
            return $this->users->getEmail();
        }
    }

    /**
     * From the entity using this trait , create a stripe customer.
     *
     * @param array $options
     *
     * @return StripeCustomer
     */
    public function createStripeCustomer(array $options = []) : StripeCustomer
    {
        if ($this->hasStripeId()) {
            throw CustomerException::exists($this);
        }

        if (!array_key_exists('email', $options) && $email = $this->stripeEmail()) {
            $options['email'] = $email;
        }

        // Here we will create the customer instance on Stripe and store the ID of the
        // user from Stripe. This ID will correspond with the Stripe user instances
        // and allow us to retrieve users from Stripe later when we need to work.
        $customer = StripeCustomer::create(
            $options,
            $this->stripeOptions()
        );

        $this->stripe_id = $customer->id;

        $this->saveOrFail();

        return $customer;
    }

    /**
     * Update entity customer info.
     *
     * @param array $options
     *
     * @return StripeCustomer
     */
    public function updateStripeCustomer(array $options = []) : StripeCustomer
    {
        return StripeCustomer::update(
            $this->stripe_id,
            $options,
            $this->stripeOptions()
        );
    }

    /**
     * Get the stripe customer info for this current entity.
     *
     * @return StripeCustomer
     */
    public function getStripeCustomerInfo() : StripeCustomer
    {
        $this->isCustomer();

        return StripeCustomer::retrieve($this->stripe_id, $this->stripeOptions());
    }

    /**
     * Get the customer info or create it if it doesn't exist.
     *
     * @param array $options
     *
     * @return StripeCustomer
     */
    public function createOrGetStripeCustomerInfo(array $options = []) : StripeCustomer
    {
        if ($this->hasStripeId()) {
            return $this->getStripeCustomerInfo();
        }

        return $this->createStripeCustomer($options);
    }

    /**
     * Apply a coupon to this customer.
     *
     * @param string $coupon
     *
     * @return void
     */
    public function applyCoupon(string $coupon) : StripeCustomer
    {
        $this->isCustomer();

        $customer = $this->getStripeCustomerInfo();

        $customer->coupon = $coupon;

        $customer->save();

        return $customer;
    }

    /**
     * Get the stripe portal billing url.
     *
     * @param string $returnUrl
     *
     * @return StripeBillingPortalSession
     */
    public function getStripeBillingPortalUrl(string $returnUrl = null) : StripeBillingPortalSession
    {
        $this->isCustomer();

        return StripeBillingPortalSession::create([
            'customer' => $this->stripeId(),
            'return_url' => $returnUrl,
        ], Cashier::stripeOptions())['url'];
    }

    /**
     * Determine if the customer is not exempted from taxes.
     *
     * @return bool
     */
    public function isNotTaxExempt() : bool
    {
        return $this->getStripeCustomerInfo()->tax_exempt === StripeCustomer::TAX_EXEMPT_NONE;
    }

    /**
     * Determine if the customer is exempted from taxes.
     *
     * @return bool
     */
    public function isTaxExempt() : bool
    {
        return $this->getStripeCustomerInfo()->tax_exempt === StripeCustomer::TAX_EXEMPT_EXEMPT;
    }

    /**
     * Determine if reverse charge applies to the customer.
     *
     * @return bool
     */
    public function reverseChargeApplies()
    {
        return $this->getStripeCustomerInfo()->tax_exempt === StripeCustomer::TAX_EXEMPT_REVERSE;
    }
}
