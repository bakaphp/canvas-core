<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Stripe\Token as StripeToken;
use Canvas\Models\PaymentMethods;

class PaymentMethodsCreds extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $payment_methods_id;

    /**
     *
     * @var string
     */
    public $payment_ending_numbers;

    /**
     *
     * @var string
     */
    public $expiration_date;

    /**
     *
     * @var string
     */
    public $zip_code;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     *
     * @var integer
     */
    public $is_deleted;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('payment_methods_creds');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'payment_methods_creds';
    }

    /**
     * Returns the current payment method credentials
     *
     * @return string
     */
    public function getCurrentPaymentMethodCreds(): self
    {
        return self::findFirstOrFail([
            "conditions"=> "users_id = ?0 and companies_id = ?1 and apps_id = ?2 and is_deleted = 0",
            "bind" => [Di::getDefault()->getUserData()->getId(),Di::getDefault()->getUserData()->getDefaultCompany()->getId(),Di::getDefault()->getApp()->getId()],
            "order"=> "id DESC"
        ]);
    }

    /**
     * Create a new record from Stripe Token
     *
     * @param string $token
     * @return return self
     */
    public static function createByStripeToken(string $token): self
    {
        $ccinfo = self::getCardInfoFromStripe($token);

        $paymentMethodCred = new self();
        $paymentMethodCred->users_id = Di::getDefault()->getUserData()->getId();
        $paymentMethodCred->companies_id = Di::getDefault()->getUserData()->getDefaultCompany()->getId();
        $paymentMethodCred->apps_id = Di::getDefault()->getApp()->getId();
        $paymentMethodCred->payment_methods_id = PaymentMethods::getDefault()->getId();
        $paymentMethodCred->payment_ending_numbers = $ccinfo->card->last4;
        $paymentMethodCred->expiration_date = $ccinfo->card->exp_year . '-' . $ccinfo->card->exp_month . '-' .  '01';
        $paymentMethodCred->zip_code = $ccinfo->card->address_zip;
        $paymentMethodCred->created_at = date('Y-m-d H:m:s');
        $paymentMethodCred->saveOrFail();

        return $paymentMethodCred;
    }

    /**
     * Get Credit Card information from Stripe
     *
     * @param string $token
     * @return return StripeToken
     */
    private function getCardInfoFromStripe(string $token): StripeToken
    {
        return StripeToken::retrieve($token,[
            'api_key' => Di::getDefault()->getConfig()->stripe->secret
        ]);
    }
}
