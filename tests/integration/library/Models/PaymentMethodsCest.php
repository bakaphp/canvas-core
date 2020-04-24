<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\PaymentMethods;
use IntegrationTester;

class PaymentMethodsCest
{
    public function getDefault(IntegrationTester $I)
    {
        $paymentMethod = PaymentMethods::getDefault();

        $I->assertTrue($paymentMethod instanceof PaymentMethods);
    }
}
