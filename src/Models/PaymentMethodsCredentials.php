<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;

class PaymentMethodsCredentials extends AbstractModel
{
    public int $users_id;
    public int $companies_groups_id;
    public string $stripe_card_id;
    public int $apps_id;
    public int $payment_methods_id;
    public int $is_default = 0;
    public string $payment_ending_numbers;
    public string $expiration_date;
    public ?string $zip_code = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('payment_methods_credentials');
    }

    /**
     * Get default payment method.
     *
     * @return self
     */
    public function getDefaultPaymentMethod() : self
    {
        return self::findFirstOrFail([
            'conditions' => 'companies_groups_id = ?1 and apps_id = ?2 and is_deleted = 0 and is_default = 1',
            'bind' => [
                Di::getDefault()->get('userData')->getDefaultCompany()->getDefaultCompanyGroup()->getId(),
                Di::getDefault()->get('app')->getId()
            ],
            'order' => 'id DESC'
        ]);
    }
}
