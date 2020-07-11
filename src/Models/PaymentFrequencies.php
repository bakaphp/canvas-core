<?php
declare(strict_types=1);

namespace Canvas\Models;

class PaymentFrequencies extends AbstractModel
{
    public string $name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('payment_frequencies');

        $this->hasMany(
            'id',
            'Canvas\Models\AppsPlans',
            'payment_frequencies_id',
            ['alias' => 'plans']
        );
    }
}
