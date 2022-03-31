<?php

declare(strict_types=1);

namespace Canvas\Contracts\Cashier;

use Canvas\Models\PaymentMethods;
use Canvas\Models\PaymentMethodsCredentials;
use Phalcon\Di;
use Stripe\Customer as StripeCustomer;
use Stripe\Token as StripeToken;

trait PaymentMethodsTrait
{
    /**
     * Create a stripe card.
     *
     * @param array $option
     *
     * @return StripeToken
     */
    public function createCreditCard(array $option) : StripeToken
    {
        $creditCardToken = StripeToken::create($option, ['api_key' => $this->getStripeKey()]);
        $this->updatePaymentMethod($creditCardToken);
        return $creditCardToken;
    }

    /**
     * Update customer's credit card.
     *
     * @param  string $token
     *
     * @return void
     */
    public function updateCreditCard(string $token) : StripeToken
    {
        $customer = $this->getStripeCustomerInfo();

        $token = StripeToken::retrieve($token, ['api_key' => $this->getStripeKey()]);

        // If the given token already has the card as their default source, we can just
        // bail out of the method now. We don't need to keep adding the same card to
        // the user's account each time we go through this particular method call.
        if ($token->card->id === $customer->default_source) {
            return $token;
        }

        $card = $customer->sources->create(['source' => $token]);
        $customer->default_source = $card->id;

        $customer->save();
        $this->updatePaymentMethod($token);

        return $token;
    }

    /**
     * Update default payment method with new card.
     *
     * @param string $customerId
     * @param string $token
     *
     * @return StripeCustomer
     */
    public function updateDefaultCreditCard(string $token) : StripeCustomer
    {
        $customer = $this->getStripeCustomerInfo();

        return StripeCustomer::update($customer->id, ['source' => $token], $this->getStripeKey());
    }

    /**
     * Given a token create a payment method.
     *
     * @param StripeToken $ccInfo
     *
     * @return PaymentMethodsCredentials
     */
    protected function updatePaymentMethod(StripeToken $cardInfo) : PaymentMethodsCredentials
    {
        $paymentMethodCred = PaymentMethodsCredentials::findFirstOrCreate([
            'conditions' => 'companies_groups_id = :companies_groups_id: and apps_id = :apps_id: and stripe_card_id = :stripe_card_id: and is_deleted = 0',
            'bind' => [
                'companies_groups_id' => $this->getId(),
                'apps_id' => Di::getDefault()->get('app')->getId(),
                'stripe_card_id' => $cardInfo->card->id,
            ]
        ], [
            'stripe_card_id' => $cardInfo->card->id,
            'users_id' => $this->users_id,
            'companies_groups_id' => $this->getId(),
            'apps_id' => Di::getDefault()->get('app')->getId(),
            'payment_methods_id' => PaymentMethods::getDefault()->getId(),
            'payment_ending_numbers' => $cardInfo->card->last4,
            'payment_methods_brand' => $cardInfo->card->brand,
            'expiration_date' => $cardInfo->card->exp_year . '-' . $cardInfo->card->exp_month,
            'zip_code' => $cardInfo->card->address_zip,
        ]);

        return $paymentMethodCred;
    }
}
