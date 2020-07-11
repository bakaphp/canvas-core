<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Stripe\Token as StripeToken;

class PaymentMethodsCreds extends AbstractModel
{

    public int $users_id;
    public int $companies_id;
    public int $apps_id;
    public int $payment_methods_id;
    public string $payment_ending_numbers;
    public string $expiration_date;
    public ?string $zip_code = null;



    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('payment_methods_creds');
    }

    /**
     * Returns the current payment method credentials.
     *
     * @return string
     */
    public function getCurrentPaymentMethodCreds() : self
    {
        return self::findFirstOrFail([
            'conditions' => 'users_id = ?0 and companies_id = ?1 and apps_id = ?2 and is_deleted = 0',
            'bind' => [
                Di::getDefault()->getUserData()->getId(),
                Di::getDefault()->getUserData()->getDefaultCompany()->getId(),
                Di::getDefault()->getApp()->getId()
            ],
            'order' => 'id DESC'
        ]);
    }

    /**
     * Create a new record from Stripe Token.
     *
     * @param string $token
     *
     * @return return self
     */
    public static function createByStripeToken(string $token) : self
    {
        $ccInfo = self::getCardInfoFromStripe($token);

        $paymentMethodCred = new self();
        $paymentMethodCred->users_id = Di::getDefault()->getUserData()->getId();
        $paymentMethodCred->companies_id = Di::getDefault()->getUserData()->getDefaultCompany()->getId();
        $paymentMethodCred->apps_id = Di::getDefault()->getApp()->getId();
        $paymentMethodCred->payment_methods_id = PaymentMethods::getDefault()->getId();
        $paymentMethodCred->payment_ending_numbers = $ccInfo->card->last4;
        $paymentMethodCred->payment_methods_brand = $ccInfo->card->brand;
        $paymentMethodCred->expiration_date = $ccInfo->card->exp_year . '-' . $ccInfo->card->exp_month . '-' . '01';
        $paymentMethodCred->zip_code = $ccInfo->card->address_zip;
        $paymentMethodCred->created_at = date('Y-m-d H:m:s');
        $paymentMethodCred->saveOrFail();

        return $paymentMethodCred;
    }

    /**
     * Get Credit Card information from Stripe.
     *
     * @param string $token
     *
     * @return return StripeToken
     */
    private function getCardInfoFromStripe(string $token) : StripeToken
    {
        return StripeToken::retrieve($token, [
            'api_key' => Di::getDefault()->getConfig()->stripe->secret
        ]);
    }
}
